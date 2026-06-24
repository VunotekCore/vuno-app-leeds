<?php

namespace Controllers;

use Core\Request;
use Core\Response;
use Models\Lead;
use Models\Template;
use PDOException;

class LeadController
{
  public function index(): void
  {
    $page = (int)(Request::query('page', 1));
    $perPage = (int)(Request::query('per_page', 50));
    $filters = [
      'search'         => Request::query('search'),
      'contact_status' => Request::query('contact_status'),
    ];

    $result = Lead::findAll($filters, $page, $perPage);

    Response::paginated($result['data'], $result['total'], $page, $perPage);
  }

  public function show(array $params): void
  {
    $lead = Lead::findById($params['id']);

    if (!$lead) {
      Response::error('Lead not found', 404);
    }

    Response::success($lead);
  }

  public function store(): void
  {
    $data = Request::body();

    $required = ['store_name', 'profile_url', 'phone'];
    $errors = [];

    foreach ($required as $field) {
      if (empty($data[$field])) {
        $errors[$field] = "$field is required";
      }
    }

    if (!empty($errors)) {
      Response::error('Validation failed', 422, $errors);
    }

    $data['phone'] = sanitize_phone($data['phone']);

    $duplicate = Lead::checkDuplicate($data['phone'], $data['profile_url']);
    if ($duplicate) {
      Response::error('Duplicate lead: a lead with this phone and profile URL already exists', 409, [
        'existing_lead' => $duplicate,
      ]);
    }

    $data['created_by'] = $_REQUEST['auth_user']['sub'];

    try {
      $uuid = Lead::create($data);
      $lead = Lead::findById($uuid);

      Response::success($lead, 'Lead created successfully', 201);
    } catch (PDOException $e) {
      if ($e->getCode() === '23000') {
        Response::error('Duplicate entry: this lead already exists', 409);
      }
      Response::error('Failed to create lead', 500);
    }
  }

  public function update(array $params): void
  {
    $lead = Lead::findById($params['id']);
    if (!$lead) {
      Response::error('Lead not found', 404);
    }

    $data = Request::body();

    try {
      Lead::update($params['id'], $data);
      $updated = Lead::findById($params['id']);

      Response::success($updated, 'Lead updated successfully');
    } catch (PDOException $e) {
      if ($e->getCode() === '23000') {
        Response::error('Duplicate entry', 409);
      }
      Response::error('Failed to update lead', 500);
    }
  }

  public function destroy(array $params): void
  {
    $lead = Lead::findById($params['id']);
    if (!$lead) {
      Response::error('Lead not found', 404);
    }

    Lead::delete($params['id']);

    Response::success(null, 'Lead deleted successfully');
  }

  public function checkDuplicate(): void
  {
    $phone = Request::query('phone');
    $profileUrl = Request::query('profile_url');

    if (!$phone && !$profileUrl) {
      Response::error('Provide phone or profile_url to check', 422);
    }

    $sanitizedPhone = $phone ? sanitize_phone($phone) : '';

    $existing = Lead::checkDuplicate($sanitizedPhone, $profileUrl ?? '');

    Response::success([
      'exists' => $existing !== null,
      'lead'   => $existing,
    ]);
  }

  public function pageData(): void
  {
    $leadData = Lead::getPageData();
    $templates = \Models\Template::findAll();
    $tiers = \Models\Tier::findAll();
    $products = \Models\Product::findAll();

    Response::success([
      'leads'     => $leadData['leads'],
      'counts'    => $leadData['counts'],
      'templates' => $templates,
      'tiers'     => $tiers,
      'products'  => $products,
    ]);
  }

  public function sendMessage(array $params): void
  {
    $lead = Lead::findById($params['id']);
    if (!$lead) {
      Response::error('Lead not found', 404);
    }

    $data = Request::body();

    $template = null;
    $renderedMessage = null;

    if (!empty($data['template_id'])) {
      $template = Template::findById($data['template_id']);
      if (!$template) {
        Response::error('Template not found', 404);
      }

      $tierPrices = require __DIR__ . '/../config/app.php';
      $tierPrice = $tierPrices['tier_mapping'][$lead['tier_classification']] ?? '';

      $renderedMessage = render_template($template['message_body'], [
        'StoreName'   => $lead['store_name'],
        'TierPrice'   => $tierPrice,
        'ProductName' => $lead['product_name'] ?? '',
      ]);
    }

    if (!$renderedMessage) {
      Response::error('No message to send', 422);
    }

    // Callmebot API integration (preserved for future use)
    if (false) {
      $userId = $_REQUEST['auth_user']['sub'];
      $user = \Models\User::findById($userId);
      $apikey = $user['whatsapp_apikey'] ?? '';

      if (empty($apikey)) {
        Response::error('WhatsApp API key not configured. Set it in Users.', 400);
      }

      $phone = '+' . $lead['phone'];
      $apiUrl = 'https://api.callmebot.com/whatsapp.php?' . http_build_query([
        'phone'  => $phone,
        'text'   => $renderedMessage,
        'apikey' => $apikey,
      ]);

      $result = @file_get_contents($apiUrl);

      if ($result === false) {
        Response::error('Failed to contact WhatsApp API', 502);
      }

      if (str_contains($result, 'APIKey is invalid')) {
        Response::error('WhatsApp API key is invalid. Update it in Users.', 400);
      }
    }

    $waUrl = sprintf('https://wa.me/%s?text=%s', $lead['phone'], rawurlencode($renderedMessage));

    $updated = Lead::advanceContact($params['id']);

    if (!$updated) {
      Response::error('Failed to update contact status', 500);
    }

    $lead = Lead::findById($params['id']);

    Response::success([
      'lead'             => $lead,
      'rendered_message' => $renderedMessage,
      'wa_url'           => $waUrl,
    ], 'Message sent successfully');
  }
}

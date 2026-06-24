<?php

namespace Controllers;

use Core\Response;
use Core\Request;
use Models\LeadNote;

class LeadNoteController
{
  public function index(array $params): void
  {
    $notes = LeadNote::findByLead($params['id']);

    Response::success(['notes' => $notes]);
  }

  public function store(array $params): void
  {
    $body = Request::body();

    if (empty($body['note']) || trim($body['note']) === '') {
      Response::error('Note is required', 422);
      return;
    }

    $createdBy = $_REQUEST['auth_user']['sub'] ?? null;
    if (!$createdBy) {
      Response::error('Unauthorized', 401);
      return;
    }

    $id = LeadNote::create($params['id'], trim($body['note']), $createdBy);

    $notes = LeadNote::findByLead($params['id']);

    Response::success(['notes' => $notes], 'Note added', 201);
  }

  public function destroy(array $params): void
  {
    $deleted = LeadNote::delete($params['noteId']);

    if (!$deleted) {
      Response::error('Note not found', 404);
      return;
    }

    Response::success([], 'Note deleted');
  }
}

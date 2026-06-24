const COUNTRY_CODE = '505'

export function sanitizePhone(input) {
  const cleaned = input.replace(/\D/g, '')

  if (cleaned.length === 8) {
    return COUNTRY_CODE + cleaned
  }

  return cleaned
}

export function formatPhone(input) {
  const raw = input.replace(/\D/g, '')
  if (raw.length === 11) {
    return `+${raw.slice(0, 3)} ${raw.slice(3, 7)} ${raw.slice(7)}`
  }
  if (raw.length === 8) {
    return `+${COUNTRY_CODE} ${raw.slice(0, 4)} ${raw.slice(4)}`
  }
  return raw
}

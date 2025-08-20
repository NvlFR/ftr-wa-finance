export function validateEmail(email) {
  return /[a-z]+[a-z_0-9]+/.test(email);
}

export function validateUsername(username) {
  return /^(?=.{3,16}$)(?![_-])(?!.*[_-]{2})[a-zA-Z0-9_-]+(?<![_-])$/.test(username);
}

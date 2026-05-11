# TODO - User Management + Dark Mode Visibility

## Completed
- [ ] N/A

## Next Steps
- [x] Update `style.css` to improve dark-mode readability for the Account Settings modal (labels, placeholders, message boxes).
- [x] Update `admin.html` to add a new sidebar tab: “Add User” and create a corresponding main section containing an Add User form and a users list container.
- [x] Update `admin.js` to:
  - [x] Load/render the full users list (using `backend/get_users.php`) in the new Add User section.
  - [x] Implement the Add User form submission (POST to `backend/register.php`) and refresh the users list on success.
  - [x] Add basic HTML escaping when rendering user data.

- [ ] Quick test flow:
  - [ ] Open `admin.html`, switch to dark theme, verify modal text visibility.
  - [ ] Go to “Add User” tab, verify users table shows birth date and age.
  - [ ] Create a new user and verify it appears in the list.


# Login Logic Fixes

## Issues Fixed
- [x] Fixed Vue 3 composition API usage in loginView.vue: Changed `this.username` and `this.password` to `username.value` and `password.value`
- [x] Fixed field name mismatch: Updated auth store to send `nombre_usuario`, `contraseña_administrador`, and `pin` instead of `username` and `password`
- [x] Fixed error message assignment: Changed `this.errorMsg` to `errorMsg.value` in loginView.vue

## Testing
- [ ] Test login with admin user (using contraseña_administrador)
- [ ] Test login with non-admin user (using pin)
- [ ] Verify error messages display correctly

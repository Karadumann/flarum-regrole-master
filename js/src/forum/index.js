import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import SignUpModal from 'flarum/forum/components/SignUpModal';
import RegistrationRoleSelector from './components/RegistrationRoleSelector';

app.initializers.add('karadumann/flarum-advanced-registration-roles', () => {
  // Extend SignUpModal to include role selection
  extend(SignUpModal.prototype, 'fields', function (items) {
    const settings = app.forum.data.attributes['karadumann-advanced-registration-roles'];
    
    if (!settings || !settings.allowedRoleIds || settings.allowedRoleIds.length === 0) {
      return;
    }

    items.add('registrationRoles', 
      m(RegistrationRoleSelector, {
        settings: settings,
        onRoleChange: (roles) => {
          this.registrationRoles = roles;
        }
      }), 
      -10 // Add before submit button
    );
  });

  // Extend SignUpModal to include registration roles in submission
  extend(SignUpModal.prototype, 'submitData', function (data) {
    if (this.registrationRoles && this.registrationRoles.length > 0) {
      data.registrationRoles = this.registrationRoles;
    }
  });

  // Initialize registration roles array
  extend(SignUpModal.prototype, 'oninit', function () {
    this.registrationRoles = [];
  });
});
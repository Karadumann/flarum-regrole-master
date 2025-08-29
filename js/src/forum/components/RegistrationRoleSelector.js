import Component from 'flarum/common/Component';
import Button from 'flarum/common/components/Button';
import LoadingIndicator from 'flarum/common/components/LoadingIndicator';

export default class RegistrationRoleSelector extends Component {
  oninit(vnode) {
    super.oninit(vnode);

    this.settings = this.attrs.settings;
    this.onRoleChange = this.attrs.onRoleChange;
    this.selectedRoles = [];
    this.availableRoles = [];
    this.loading = true;
    this.error = null;

    this.loadAvailableRoles();
  }

  view() {
    if (this.loading) {
      return m('.RegistrationRoleSelector', [
        m('.RegistrationRoleSelector-loading', [
          LoadingIndicator.component({ size: 'small' }),
          m('span', app.translator.trans('karadumann-advanced-registration-roles.forum.loading_roles'))
        ])
      ]);
    }

    if (this.error) {
      return m('.RegistrationRoleSelector', [
        m('.RegistrationRoleSelector-error', [
          m('i.fas.fa-exclamation-triangle'),
          m('span', this.error)
        ])
      ]);
    }

    if (this.availableRoles.length === 0) {
      return null;
    }

    return m('.RegistrationRoleSelector', [
      this.settings.registrationTitle && m('h3.RegistrationRoleSelector-title', this.settings.registrationTitle),
      
      this.settings.registrationDescription && m('.RegistrationRoleSelector-description', this.settings.registrationDescription),
      
      m('.RegistrationRoleSelector-roles', this.availableRoles.map(role => 
        this.renderRoleCard(role)
      )),
      
      this.settings.forceRoleSelection && this.selectedRoles.length === 0 && m('.RegistrationRoleSelector-required', [
        m('i.fas.fa-info-circle'),
        m('span', app.translator.trans('karadumann-advanced-registration-roles.forum.role_selection_required'))
      ])
    ]);
  }

  renderRoleCard(role) {
    const isSelected = this.selectedRoles.includes(role.id());
    const canSelect = this.settings.allowMultipleRoles || this.selectedRoles.length === 0 || isSelected;

    return m('.RoleCard', {
      className: [
        isSelected ? 'RoleCard--selected' : '',
        !canSelect ? 'RoleCard--disabled' : '',
        'RoleCard--clickable'
      ].filter(Boolean).join(' '),
      onclick: canSelect ? () => this.toggleRole(role.id()) : null,
      style: {
        borderColor: isSelected ? (role.registrationColor || role.color() || '#3498db') : undefined
      }
    }, [
      m('.RoleCard-header', {
        style: {
          backgroundColor: isSelected ? (role.registrationColor || role.color() || '#3498db') : '#f8f9fa'
        }
      }, [
        this.settings.showRoleIcons && role.registrationIcon && m('i.RoleCard-icon', {
          className: role.registrationIcon
        }),
        m('h3.RoleCard-title', role.nameSingular()),
        m('.RoleCard-selector', [
          m('input[type=checkbox]', {
            checked: isSelected,
            disabled: !canSelect,
            onchange: canSelect ? () => this.toggleRole(role.id()) : null
          })
        ])
      ]),
      
      this.settings.showRoleDescriptions && role.registrationDescription && m('.RoleCard-description', role.registrationDescription),
      
      role.color() && m('.RoleCard-badge', {
        style: { backgroundColor: role.color() }
      })
    ]);
  }

  toggleRole(roleId) {
    const index = this.selectedRoles.indexOf(roleId);
    
    if (index > -1) {
      // Remove role
      this.selectedRoles.splice(index, 1);
    } else {
      // Add role
      if (this.settings.allowMultipleRoles) {
        this.selectedRoles.push(roleId);
      } else {
        this.selectedRoles = [roleId];
      }
    }
    
    this.onRoleChange(this.selectedRoles);
    m.redraw();
  }

  loadAvailableRoles() {
    app.request({
      method: 'GET',
      url: app.forum.attribute('apiUrl') + '/registration-roles'
    }).then(response => {
      this.availableRoles = app.store.pushPayload(response).sort((a, b) => {
        const orderA = a.registrationOrder || 0;
        const orderB = b.registrationOrder || 0;
        
        if (orderA !== orderB) {
          return orderA - orderB;
        }
        
        return a.nameSingular().localeCompare(b.nameSingular());
      });
      
      this.loading = false;
      m.redraw();
    }).catch(error => {
      console.error('Failed to load registration roles:', error);
      this.error = app.translator.trans('karadumann-advanced-registration-roles.forum.load_error');
      this.loading = false;
      m.redraw();
    });
  }
}
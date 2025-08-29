import ExtensionPage from 'flarum/admin/components/ExtensionPage';
import Switch from 'flarum/common/components/Switch';
import Button from 'flarum/common/components/Button';
import Group from 'flarum/common/models/Group';
import GroupDescriptionModal from './GroupDescriptionModal';
import Stream from 'flarum/common/utils/Stream';

export default class AdvancedRegistrationRolesSettingsPage extends ExtensionPage {
  oninit(vnode) {
    super.oninit(vnode);

    this.allowedRoleIds = Stream(this.setting('karadumann-advanced-registration-roles.allowed_role_ids', '[]'));
    this.allowMultipleRoles = Stream(this.setting('karadumann-advanced-registration-roles.allow_multiple_roles', false));
    this.forceRoleSelection = Stream(this.setting('karadumann-advanced-registration-roles.force_role_selection', false));
    this.showRoleDescriptions = Stream(this.setting('karadumann-advanced-registration-roles.show_role_descriptions', true));
    this.showRoleIcons = Stream(this.setting('karadumann-advanced-registration-roles.show_role_icons', true));
    this.registrationTitle = Stream(this.setting('karadumann-advanced-registration-roles.registration_title', ''));
    this.registrationDescription = Stream(this.setting('karadumann-advanced-registration-roles.registration_description', ''));

    this.groups = app.store.all('groups').filter(group => 
      group.id() !== Group.GUEST_ID && 
      group.id() !== Group.MEMBER_ID &&
      !group.isHidden()
    );
  }

  content() {
    return [
      m('.container', [
        m('.AdvancedRegistrationRolesSettingsPage', [
          m('.Form-group', [
            m('h3', app.translator.trans('karadumann-advanced-registration-roles.admin.settings.general_title')),
            m('.helpText', app.translator.trans('karadumann-advanced-registration-roles.admin.settings.general_description'))
          ]),

          m('.Form-group', [
            m('label', app.translator.trans('karadumann-advanced-registration-roles.admin.settings.registration_title')),
            m('input.FormControl', {
              type: 'text',
              value: this.registrationTitle(),
              oninput: (e) => this.registrationTitle(e.target.value),
              placeholder: app.translator.trans('karadumann-advanced-registration-roles.admin.settings.registration_title_placeholder')
            })
          ]),

          m('.Form-group', [
            m('label', app.translator.trans('karadumann-advanced-registration-roles.admin.settings.registration_description')),
            m('textarea.FormControl', {
              value: this.registrationDescription(),
              oninput: (e) => this.registrationDescription(e.target.value),
              placeholder: app.translator.trans('karadumann-advanced-registration-roles.admin.settings.registration_description_placeholder'),
              rows: 3
            })
          ]),

          m('.Form-group', [
            Switch.component({
              state: JSON.parse(this.allowMultipleRoles()),
              onchange: (value) => this.allowMultipleRoles(value),
              children: app.translator.trans('karadumann-advanced-registration-roles.admin.settings.allow_multiple_roles')
            })
          ]),

          m('.Form-group', [
            Switch.component({
              state: JSON.parse(this.forceRoleSelection()),
              onchange: (value) => this.forceRoleSelection(value),
              children: app.translator.trans('karadumann-advanced-registration-roles.admin.settings.force_role_selection')
            })
          ]),

          m('.Form-group', [
            Switch.component({
              state: JSON.parse(this.showRoleDescriptions()),
              onchange: (value) => this.showRoleDescriptions(value),
              children: app.translator.trans('karadumann-advanced-registration-roles.admin.settings.show_role_descriptions')
            })
          ]),

          m('.Form-group', [
            Switch.component({
              state: JSON.parse(this.showRoleIcons()),
              onchange: (value) => this.showRoleIcons(value),
              children: app.translator.trans('karadumann-advanced-registration-roles.admin.settings.show_role_icons')
            })
          ]),

          m('.Form-group', [
            m('h3', app.translator.trans('karadumann-advanced-registration-roles.admin.settings.allowed_roles_title')),
            m('.helpText', app.translator.trans('karadumann-advanced-registration-roles.admin.settings.allowed_roles_description')),
            
            m('.RolesList', this.groups.map(group => 
              m('.RoleItem', {
                className: this.isRoleAllowed(group.id()) ? 'RoleItem--selected' : ''
              }, [
                m('.RoleItem-info', [
                  Switch.component({
                    state: this.isRoleAllowed(group.id()),
                    onchange: (value) => this.toggleRole(group.id(), value),
                    children: [
                      m('span.RoleItem-name', group.nameSingular()),
                      group.color() && m('span.RoleItem-badge', {
                        style: { backgroundColor: group.color() }
                      })
                    ]
                  })
                ]),
                
                this.isRoleAllowed(group.id()) && m('.RoleItem-actions', [
                  Button.component({
                    className: 'Button Button--text',
                    icon: 'fas fa-edit',
                    onclick: () => this.showGroupDescriptionModal(group),
                    children: app.translator.trans('karadumann-advanced-registration-roles.admin.settings.manage_description')
                  })
                ])
              ])
            ))
          ]),

          this.submitButton()
        ])
      ])
    ];
  }

  isRoleAllowed(groupId) {
    const allowedIds = JSON.parse(this.allowedRoleIds());
    return allowedIds.includes(groupId);
  }

  toggleRole(groupId, enabled) {
    let allowedIds = JSON.parse(this.allowedRoleIds());
    
    if (enabled) {
      if (!allowedIds.includes(groupId)) {
        allowedIds.push(groupId);
      }
    } else {
      allowedIds = allowedIds.filter(id => id !== groupId);
    }
    
    this.allowedRoleIds(JSON.stringify(allowedIds));
  }

  showGroupDescriptionModal(group) {
    app.modal.show(GroupDescriptionModal, { group });
  }

  onsaved() {
    app.alerts.show({ type: 'success' }, app.translator.trans('core.admin.basics.saved_message'));
  }
}
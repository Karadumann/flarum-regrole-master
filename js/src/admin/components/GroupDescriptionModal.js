import Modal from 'flarum/common/components/Modal';
import Button from 'flarum/common/components/Button';
import Stream from 'flarum/common/utils/Stream';

export default class GroupDescriptionModal extends Modal {
  oninit(vnode) {
    super.oninit(vnode);

    this.group = this.attrs.group;
    
    this.description = Stream(app.data.settings[`karadumann-advanced-registration-roles.group_${this.group.id()}_description`] || '');
    this.icon = Stream(app.data.settings[`karadumann-advanced-registration-roles.group_${this.group.id()}_icon`] || 'fas fa-users');
    this.color = Stream(app.data.settings[`karadumann-advanced-registration-roles.group_${this.group.id()}_color`] || this.group.color() || '#3498db');
    this.order = Stream(app.data.settings[`karadumann-advanced-registration-roles.group_${this.group.id()}_order`] || '0');
  }

  className() {
    return 'GroupDescriptionModal Modal--small';
  }

  title() {
    return app.translator.trans('karadumann-advanced-registration-roles.admin.group_modal.title', {
      group: this.group.nameSingular()
    });
  }

  content() {
    return [
      m('.Modal-body', [
        m('.Form-group', [
          m('label', app.translator.trans('karadumann-advanced-registration-roles.admin.group_modal.description_label')),
          m('textarea.FormControl', {
            value: this.description(),
            oninput: (e) => this.description(e.target.value),
            placeholder: app.translator.trans('karadumann-advanced-registration-roles.admin.group_modal.description_placeholder'),
            rows: 4
          }),
          m('.helpText', app.translator.trans('karadumann-advanced-registration-roles.admin.group_modal.description_help'))
        ]),

        m('.Form-group', [
          m('label', app.translator.trans('karadumann-advanced-registration-roles.admin.group_modal.icon_label')),
          m('.IconInput', [
            m('input.FormControl', {
              type: 'text',
              value: this.icon(),
              oninput: (e) => this.icon(e.target.value),
              placeholder: 'fas fa-users'
            }),
            m('.IconPreview', [
              m('i', { className: this.icon() })
            ])
          ]),
          m('.helpText', app.translator.trans('karadumann-advanced-registration-roles.admin.group_modal.icon_help'))
        ]),

        m('.Form-group', [
          m('label', app.translator.trans('karadumann-advanced-registration-roles.admin.group_modal.color_label')),
          m('.ColorInput', [
            m('input.FormControl', {
              type: 'color',
              value: this.color(),
              oninput: (e) => this.color(e.target.value)
            }),
            m('input.FormControl', {
              type: 'text',
              value: this.color(),
              oninput: (e) => this.color(e.target.value),
              placeholder: '#3498db',
              style: { marginLeft: '10px', width: '100px' }
            })
          ]),
          m('.ColorPreview', {
            style: {
              backgroundColor: this.color(),
              width: '30px',
              height: '30px',
              borderRadius: '4px',
              marginTop: '5px'
            }
          })
        ]),

        m('.Form-group', [
          m('label', app.translator.trans('karadumann-advanced-registration-roles.admin.group_modal.order_label')),
          m('input.FormControl', {
            type: 'number',
            value: this.order(),
            oninput: (e) => this.order(e.target.value),
            min: '0',
            step: '1'
          }),
          m('.helpText', app.translator.trans('karadumann-advanced-registration-roles.admin.group_modal.order_help'))
        ]),

        m('.PreviewSection', [
          m('h4', app.translator.trans('karadumann-advanced-registration-roles.admin.group_modal.preview_title')),
          m('.RolePreview', {
            className: 'RoleCard RoleCard--preview',
            style: { borderColor: this.color() }
          }, [
            m('.RoleCard-header', {
              style: { backgroundColor: this.color() }
            }, [
              m('i.RoleCard-icon', { className: this.icon() }),
              m('h3.RoleCard-title', this.group.nameSingular())
            ]),
            this.description() && m('.RoleCard-description', this.description())
          ])
        ])
      ]),

      m('.Modal-footer', [
        Button.component({
          type: 'button',
          className: 'Button Button--primary',
          loading: this.loading,
          onclick: this.save.bind(this),
          children: app.translator.trans('karadumann-advanced-registration-roles.admin.group_modal.save_button')
        }),
        Button.component({
          type: 'button',
          className: 'Button',
          onclick: this.hide.bind(this),
          children: app.translator.trans('core.admin.modal.cancel')
        })
      ])
    ];
  }

  save() {
    this.loading = true;

    const settings = {};
    settings[`karadumann-advanced-registration-roles.group_${this.group.id()}_description`] = this.description();
    settings[`karadumann-advanced-registration-roles.group_${this.group.id()}_icon`] = this.icon();
    settings[`karadumann-advanced-registration-roles.group_${this.group.id()}_color`] = this.color();
    settings[`karadumann-advanced-registration-roles.group_${this.group.id()}_order`] = this.order();

    app.request({
      method: 'POST',
      url: app.forum.attribute('apiUrl') + '/settings',
      body: settings
    }).then(() => {
      // Update local settings
      Object.assign(app.data.settings, settings);
      
      app.alerts.show({ type: 'success' }, app.translator.trans('karadumann-advanced-registration-roles.admin.group_modal.saved_message'));
      this.hide();
    }).catch(() => {
      this.loading = false;
      m.redraw();
    });
  }
}
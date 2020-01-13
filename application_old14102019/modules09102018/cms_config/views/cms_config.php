<script>

      var form = Ext.create({
        xtype: 'form',
        bodyPadding: 15,
        header: false,
        items: [{
          xtype: 'combobox',
          anchor: '100%',
          fieldLabel: 'SIZE',
          // store: {
          //   type: 'Activities'
          // },
          // valueField: 'REFF_ID',
          // displayField: 'REFF_NAME',
          allowBlank: false,
          name: 'ACTIVITY',
        }],
        buttons: [{
          text: 'Reset',
          icon: Contants.getImg() + "icons/search.png",
          handler: function() {
            this.up('form').getForm().reset();
          }
        }, {
          text: 'Save',
          icon: Contants.getImg() + "icons/save_flat.png",
          formBind: true,
          handler: function() {}
        }]
      });
      form.show();

</script>

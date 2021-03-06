Ext.namespace('CB');
/*
    An api should be defined for read and submitting form
    for locking mechanism there should be specified lockEdit and unlockEdit functions.
    unlockEdit should call doClose or destroy the form at the end of unlock process

*/
CB.GenericForm = Ext.extend(Ext.FormPanel, {
    autoScroll: false
    ,closable: true
    ,border: false
    ,bodyStyle: 'padding: 10px'
    ,title: 'Generic window'
    ,monitorValid: true
    ,data: {}
    ,initComponent: function(){
        CB.GenericForm.superclass.initComponent.apply(  this, arguments );
        this.addEvents('savesuccess', 'savefail', 'change');
        this.enableBubble('savesuccess');

        this.on('beforeclose', this.onBeforeClose, this);
        this.on('afterrender', this.loadData, this);
        this.on('change', this.setDirty, this);
    }
    ,setDirty: function(isDirty){
        this._isDirty = (isDirty !== false);
    }
    ,_lockEdit: function(){
        if(this.lockEdit) return this.lockEdit();
    }
    ,_unlockEdit: function(){
        if(this.unlockEdit) return this.unlockEdit();
        this.doClose();
    }
    ,onBeforeClose: function(){
        if(this._confirmedClosing || !this._isDirty){
            this.getEl().mask(L.Closing + ' ...', 'x-mask-loading');
            if(!Ext.isNumber(this.data.id)) this.doClose(); else this._unlockEdit();
            return false;
        }
        Ext.Msg.show({
            title:  L.Confirmation
            ,msg:   L.SavingChangedDataMessage
            ,icon:  'ext-mb-question'
            ,buttons: Ext.Msg.YESNOCANCEL
            ,scope: this
            ,fn: function(b, text, opt){
                switch(b){
                case 'yes':
                    this._confirmedClosing = true;
                    this.saveForm();
                    break;
                case 'no':
                    this._confirmedClosing = true;
                    this._unlockEdit();
                    break;
                }
            }
        });
        return false;
    }
    ,doClose: function(){
        this.suspendEvents(false);
        this.destroy();
    }
    ,loadData: function(){
        if(isNaN(this.data.id)){
            this.data.id = Ext.isEmpty(this.data.id) ? Ext.id(): this.data.id;
            this._setFormValues();
            this.setDirty(true); // because it's a new form and needs to be saved
            this.getEl().unmask();
            return;
        }
        this.getForm().load({
            params: {
                id: this.data.id
            }
            ,scope: this
            ,success: this.processLoadResponse
            ,failure: this.processLoadResponse.createDelegate(this)
        });
    }
    ,getTitle: function(){
        if(!Ext.isEmpty(this.data.name)) {
            return this.data.name;
        } else if(!isNaN(this.data.id)) {
            return '&lt;'+L.noName+'&gt; (id: '+this.data.id+')';
        } else {
            return '&lt;'+L.noName+'&gt;';
        }
    }
    ,updateFormTitle: function(){
        t = '';
        if(this.data && !Ext.isEmpty(this.data.date_start)) t = Date.parseDate(this.data.date_start.substr(0, 10), 'Y-m-d').format(App.dateFormat) + '. ';
        t += this.data.new_title ? this.data.new_title : this.getTitle();
        this.setTitle(App.shortenString(t, 35));
        i = Ext.value(this.data.iconCls, Ext.value(this.iconCls, ''));
        if(i == 'icon-loading') i = '';
        if(Ext.isEmpty(i) && this.getIconClass ) i = this.getIconClass();
        this.setIconClass( i );
    }
    ,getIconClass: Ext.emptyFn // this function should be redefined for child classes to return a corresponding icon for the window
    ,processLoadResponse: function(f, e){
        this.getEl().unmask();
        r = e.result;
        if(r.success !== true){
            if(App.hideFailureAlerts){
                this.doClose();
                return;
            }
            Ext.Msg.confirm( L.Error, Ext.value(e.msg, L.readDataErrorMessage), function(b){ if(b == 'yes') this.loadData(); else this.doClose(); }, this );
            return;
        }
        if(!Ext.isDefined(r.data)) return;
        this.data = r.data;
        if(this.onFormLoaded) this.onFormLoaded(f, e);

        if(Ext.isDefined(this.data.already_opened_by)){
            Ext.Msg.show({
                title: L.ActionOpeningConfirmation
                ,msg: this.data.already_opened_by
                ,buttons: Ext.Msg.YESNO
                ,fn: function(b){ if(b == 'yes'){this.enable(); this._setFormValues(); this._lockEdit(); } else this._unlockEdit(); }
                ,scope: this
                ,animEl: this.getEl()
                ,icon: Ext.MessageBox.QUESTION
            });
            return;
        }
        this._setFormValues();
    }
    ,_setFormValues: function(){
        this.updateFormTitle();
        if(this.setFormValues) this.setFormValues();
        this.setDirty(false);
    }
    ,_getFormValues: function(){
        if(this.getFormValues) this.getFormValues();
    }
    ,saveForm: function(){
        if(!this.getForm().isValid()) return ;
        this.getEl().mask(L.SavingChanges + ' ...', 'x-mask-loading');
        this._getFormValues();
        this.getForm().submit({
            clientValidation: true
            ,params: {
                data: Ext.encode(this.data)
                ,close: this._confirmedClosing
                ,forcedSave: this._forcedSave
            }
            ,scope: this
            ,success: this.onSaveSuccess
            ,failure: this.onSaveFailure
        });
    }
    ,onSaveSuccess: function(f, a){
        if(Ext.isDefined(a.result.data)) this.data = a.result.data;
        if(this.onFormLoaded) this.onFormLoaded(f, a);
        if(Ext.isDefined(a.result.title)) this.title = a.result.title;
        this._setFormValues();
        this.fireEvent('savesuccess', this, a);
        if(this._confirmedClosing) return this.doClose();
        this.getEl().unmask();
    }
    ,onSaveFailure: function(form, action){
        this.getEl().unmask();
        if(Ext.isDefined(action.result.already_opened_by)){
            Ext.Msg.show({
            title: L.SavingDataConfirmation
            ,msg: action.result.already_opened_by
            ,buttons: Ext.Msg.YESNO
            ,fn: function(b){
                if(b == 'yes'){
                    this._forcedSave = 1;
                    this.saveForm();
                } else {
                    this.getEl().unmask();
                    this._confirmedClosing = 0;
                }
            }
            ,scope: this
            ,animEl: this.getEl()
            ,icon: Ext.MessageBox.QUESTION
            });
        }else{
            this.fireEvent('savefail', this, action);
            App.formSubmitFailure(form, action);
        }
    }
});

Ext.reg('CBGenericForm', CB.GenericForm);

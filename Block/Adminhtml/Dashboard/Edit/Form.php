<?php

namespace Expressly\Expressly\Block\Adminhtml\Dashboard\Edit;

use Magento\Backend\Block\Widget\Form\Generic;

class Form extends Generic
{
    protected function _construct()
    {
        parent::_construct();

        $this->setId('dashboard_form');
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create([
            'data' => [
                'id' => 'edit_form',
                'action' => $this->getData('action'),
                'method' => 'post'
            ]
        ]);

        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Preferences'),
                'class' => 'fieldset-wide'
            ]
        );

        $fieldset->addField(
            'api_key',
            'text',
            [
                'name' => 'api_key',
                'label' => __('API Key'),
                'title' => __('API Key'),
                'required' => true
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
<?php
namespace BoostCake\View\Helper;

use Cake\View\Helper\FormHelper as BaseForm;
use Cake\View\View;

class FormHelper extends BaseForm {

	const COLCOUNT = 12;

	protected $_formStyle = 'normal';
	protected $_labelWidth = 3;
	protected $_fieldWidth = 9;

	protected $_bootstrapTemplates = [
		'error' => '<div class="help-block text-danger">{{content}}</div>',
		'inputContainer' => '<div class="form-group {{type}}{{required}}">{{content}}</div>',
		'inputContainerError' => '<div class="form-group {{type}}{{required}} has-error">{{content}}{{error}}</div>',
		'submitContainer' => '<div class="submit">{{content}}</div>',
		'radioLabel' => '<label{{attrs}}>{{input}}{{text}}</label>',
		'radioWrapper' => '<div class="radio">{{label}}</div>',
		'checkboxLabel' => '<label{{attrs}}>{{input}}{{text}}</label>',
		'checkboxFormGroup' => '<div{{attrs}}>{{label}}</div>',
	];

/**
 * Construct the widgets and binds the default context providers
 *
 * @param \Cake\View\View $View   The View this helper is being attached to.
 * @param array           $config Configuration settings for the helper.
 */
	public function __construct(View $View, array $config = []) {
		$this->_defaultConfig['templates'] = array_merge($this->_defaultConfig['templates'], $this->_bootstrapTemplates);
		parent::__construct($View, $config);

		$this->addWidget('radio', ['BoostCake\View\Widget\Radio', 'label']);
	}

/**
 * {{@inheritDoc}}
 *
 * @param string $fieldName Fieldname
 * @param array $options Options
 *
 * @return string
 */
	public function input($fieldName, array $options = []) {
		$options += [
			'group' => []
		];
		$options = $this->addClass($options, 'form-control');

		return parent::input($fieldName, $options);
	}

/**
 * {{@inheritDoc}}
 *
 * @param array $options
 *
 * @return string
 */
	protected function _groupTemplate($options) {
		$options['group'] = $options['options']['group'];
		$groupTemplate = $options['options']['type'] === 'checkbox' ? 'checkboxFormGroup' : 'formGroup';

		if ($this->_formStyle == 'horizontal' && !isset($options['ignoreStyle']) && $options['options']['type'] === 'checkbox') {
			$options['group'] = $this->addClass($options['group'], 'checkbox col-sm-offset-' . $this->_labelWidth);
		}

		return $this->templater()
			->format($groupTemplate, [
				'input' => $options['input'],
				'label' => $options['label'],
				'error' => $options['error'],
				'attrs' => $this->templater()->formatAttributes($options['group'])
			]);
	}

/**
 * {{@inheritDoc}}
 *
 * @param string $fieldName Field
 * @param null $text Label text
 * @param array $options Options
 *
 * @return string
 */
	protected function _inputLabel($fieldName, $label, $options) {
		if (!is_array($label)) {
			$label = [
				'text' => $label
			];
		}

		if ($this->_formStyle == 'horizontal' && !isset($options['ignoreStyle']) && $options['type'] !== 'checkbox') {
			$label = $this->addClass($label, 'col-sm-' . $this->_labelWidth);
		}
		unset($options['ignoreStyle']);

		$templater = $this->templater();
		$currentLabel = $templater->get('label');
		if ($options['type'] === 'checkbox') {
			$templater->add([
				'label' => $templater->get('checkboxLabel')
			]);
		} else {
			$label = $this->addClass($label, 'control-label');
		}

		$output = parent::_inputLabel($fieldName, $label, $options);

		if ($options['type'] === 'checkbox') {
			$templater->add([
				'label' => $currentLabel
			]);
		}

		return $output;
	}

/**
 * {{@inheritDoc}}
 *
 * @param string $fieldName Field
 * @param array  $options   Options
 *
 * @return string
 */
	public function checkbox($fieldName, array $options = []) {
		if (isset($options['type']) && !empty($options['class']) && $options['type'] == 'checkbox') {
			$options['class'] = trim(str_replace('form-control', '', $options['class']));
			if (empty($options['class'])) {
				unset($options['class']);
			}
		}

		return parent::checkbox($fieldName, $options);
	}

	protected function _formStyleOptions($options) {
		$formStyle = $options['formStyle'];
		unset($options['formStyle']);

		switch ($formStyle) {
			case 'horizontal':
				if (isset($options['labelWidth'])) {
					$this->_labelWidth = $options['labelWidth'];
					unset($options['labelWidth']);
				}
				$this->_fieldWidth = static::COLCOUNT - $this->_labelWidth;

				$options = $this->addClass($options, 'form-horizontal');
				$this->_formStyle = 'horizontal';
				$this->templates([
					'formGroup' => '{{label}}<div class="col-sm-' . $this->_fieldWidth . '">{{input}}</div>',
					'checkboxWrapper' => '<div class="col-sm-' . $this->_fieldWidth . ' col-sm-offset-' . $this->_labelWidth . '"><div class="checkbox">{{label}}</div></div>',
					'error' => '<div class="clearfix"></div><div class="help-block text-danger col-sm-' . $this->_fieldWidth . ' col-sm-push-' . $this->_labelWidth . '">{{content}}</div>',
				]);
				break;
		}

		return $options;
	}

/**
 * {{@inheritDoc}}
 *
 * @param null  $model Context
 * @param array $options Options
 *
 * @return string
 */
	public function create($model = null, array $options = []) {
		if (isset($options['formStyle'])) {
			$options = $this->_formStyleOptions($options);
		}

		return parent::create($model, $options);
	}
}

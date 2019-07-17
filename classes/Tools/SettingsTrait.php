<?php
// Copyright (c) 2016 Interfacelab LLC. All rights reserved.
//
// Released under the GPLv3 license
// http://www.gnu.org/licenses/gpl-3.0.html
//
// Uses code from:
// Persist Admin Notices Dismissal
// by Agbonghama Collins and Andy Fragen
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// **********************************************************************

namespace ILAB\MediaCloud\Tools;

use ILAB\MediaCloud\Utilities\Environment;
use ILAB\MediaCloud\Utilities\View;
use function ILAB\MediaCloud\Utilities\arrayPath;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

trait SettingsTrait {
    /** @var string The page slug for this tool's options */
    protected $options_page;

    /** @var string The option group for this tool's options */
    protected $options_group;

    /**
     * Returns the options page that holds this tool's settings
     * @return string
     */
    public function optionsPage() {
        return $this->options_page;
    }

    /**
     * Returns the options group that holds this tool's settings
     * @return string
     */
    public function optionsGroup() {
        return $this->options_group;
    }

    protected function mergeSettings($primaryConfig, $otherConfigs) {
        $dynamicOption = arrayPath($primaryConfig, 'dynamic-config-option', null);
        if ($dynamicOption === null) {
            return $primaryConfig;
        }

        $groups = arrayPath($primaryConfig, 'settings/groups', null);
        if ($groups === null) {
            return $primaryConfig;
        }

        foreach($groups as $groupKey => $group) {
            if (arrayPath($group, 'dynamic', false)) {
                $newOptions = [];

                foreach($otherConfigs as $key => $config) {
                    $configGroup = arrayPath($config, $groupKey, null);
                    if ($configGroup === null) {
                        continue;
                    }

                    foreach($configGroup['options'] as $optionKey => $option) {
                        if (empty($newOptions[$optionKey])) {
                            $newOptions[$optionKey] = $option;
                            $newOptions[$optionKey]['conditions'] = [];
                            $newOptions[$optionKey]['conditions'][$dynamicOption] = [];
                            $newOptions[$optionKey]['conditions'][$dynamicOption][] = $key;
                        } else {
                            $newOptions[$optionKey]['conditions'][$dynamicOption][] = $key;
                        }
                    }

                }

                if (!empty($newOptions)) {
                    $options = array_merge($primaryConfig['settings']['groups'][$groupKey]['options'], $newOptions);

                    uasort($options, function($optA, $optB) {
                        $a = arrayPath($optA, 'display-order', 0);
                        $b = arrayPath($optB, 'display-order', 0);

                        if ($a == $b) {
                            return 0;
                        }

                        return ($a < $b) ? -1 : 1;
                    });

                    $primaryConfig['settings']['groups'][$groupKey]['options'] = $options;
                }
            }
        }


        return $primaryConfig;
    }

    /**
     * Registers an option that has no UI
     * @param $option
     */
    protected function registerSetting($option) {
        register_setting($this->options_group, $option);
    }

    /**
     * Registers an option with a text input UI
     * @param $option_name
     * @param $title
     * @param $settings_slug
     * @param null $description
     * @param null $placeholder
     * @param null $conditions
     */
    protected function registerTextFieldSetting($option_name, $title, $settings_slug, $description=null, $placeholder=null, $conditions=null) {
        add_settings_field($option_name,
            $title,
            [$this,'renderTextFieldSetting'],
            $this->options_page,
            $settings_slug,
            ['option'=>$option_name, 'description'=>$description, 'placeholder' => $placeholder, 'conditions' => $conditions]);
    }

    /**
     * Renders a text field
     * @param $args
     */
    public function renderTextFieldSetting($args) {
        echo View::render_view('base/fields/text-field.php',[
            'value' => Environment::Option($args['option']),
            'name' => $args['option'],
            'placeholder' => $args['placeholder'],
            'conditions' => $args['conditions'],
            'description' => (isset($args['description'])) ? $args['description'] : false
        ]);
    }

    /**
     * Registers an option with a password input
     * @param $option_name
     * @param $title
     * @param $settings_slug
     * @param null $description
     * @param null $placeholder
     * @param null $conditions
     */
    protected function registerPasswordFieldSetting($option_name,$title,$settings_slug, $description=null, $placeholder=null, $conditions=null) {
        add_settings_field($option_name,
            $title,
            [$this,'renderPasswordFieldSetting'],
            $this->options_page,
            $settings_slug,
            ['option'=>$option_name,'description'=>$description, 'placeholder'=>$placeholder, 'conditions' => $conditions]);
    }

    /**
     * Renders a password input
     * @param $args
     */
    public function renderPasswordFieldSetting($args) {
        echo View::render_view('base/fields/password.php',[
            'value' => Environment::Option($args['option']),
            'name' => $args['option'],
            'placeholder' => $args['placeholder'],
            'conditions' => $args['conditions'],
            'description' => (isset($args['description'])) ? $args['description'] : false
        ]);
    }

    /**
     * Registers an option with a textarea field
     * @param $option_name
     * @param $title
     * @param $settings_slug
     * @param null $description
     * @param null $placeholder
     * @param null $conditions
     */
    protected function registerTextAreaFieldSetting($option_name,$title,$settings_slug,$description=null, $placeholder=null, $conditions=null) {
        add_settings_field($option_name,
            $title,
            [$this,'renderTextAreaFieldSetting'],
            $this->options_page,
            $settings_slug,
            ['option'=>$option_name,'description'=>$description, 'placeholder'=>$placeholder, 'conditions' => $conditions]);
    }

    /**
     * Renders the text area
     * @param $args
     */
    public function renderTextAreaFieldSetting($args) {
        echo View::render_view('base/fields/text-area.php',[
            'value' => Environment::Option($args['option']),
            'name' => $args['option'],
            'placeholder' => $args['placeholder'],
            'conditions' => $args['conditions'],
            'description' => (isset($args['description'])) ? $args['description'] : false
        ]);
    }

    /**
     * Registers an option with a custom render callback
     * @param $option_name
     * @param $title
     * @param $settings_slug
     * @param $renderCallback
     * @param null $description
     * @param null $conditions
     */
    protected function registerCustomFieldSetting($option_name,$title,$settings_slug,$renderCallback,$description=null, $conditions=null) {
        add_settings_field($option_name,
            $title,
            [$this,$renderCallback],
            $this->options_page,
            $settings_slug,
            ['option'=>$option_name,'description'=>$description, 'conditions' => $conditions]);
    }

    /**
     * Registers an option with a checkbox input
     * @param $option_name
     * @param $title
     * @param $settings_slug
     * @param null $description
     * @param bool $default
     * @param null $conditions
     */
    protected function registerCheckboxFieldSetting($option_name,$title,$settings_slug,$description=null, $default=false, $conditions=null) {
        add_settings_field($option_name,
            $title,
            [$this,'renderCheckboxFieldSetting'],
            $this->options_page,
            $settings_slug,
            ['option'=>$option_name,'description'=>$description, 'default' => $default, 'conditions' => $conditions]);

    }

    /**
     * Renders the checkbox
     * @param $args
     */
    public function renderCheckboxFieldSetting($args) {
        echo View::render_view('base/fields/checkbox.php',[
            'value' => Environment::Option($args['option'], null, $args['default']),
            'name' => $args['option'],
            'conditions' => $args['conditions'],
            'description' => (isset($args['description'])) ? $args['description'] : false
        ]);
    }

    /**
     * Registers an option with a number input
     *
     * @param $option_name
     * @param $title
     * @param $settings_slug
     * @param null $description
     * @param bool $default
     * @param null $conditions
     * @param int $min
     * @param int $max
     * @param null $increment
     */
    protected function registerNumberFieldSetting($option_name,$title,$settings_slug,$description=null, $default=false, $conditions=null,$min = 1, $max = 1000, $increment = null) {
        add_settings_field($option_name,
            $title,
            [$this,'renderNumberFieldSetting'],
            $this->options_page,
            $settings_slug,
            ['option'=>$option_name,'description'=>$description, 'default' => $default, 'conditions' => $conditions, 'min' => $min, 'max' => $max, 'inc' => $increment]);

    }

    /**
     * Renders a number input
     * @param $args
     */
    public function renderNumberFieldSetting($args) {
        echo View::render_view('base/fields/number.php',[
            'value' => Environment::Option($args['option'], null, $args['default']),
            'name' => $args['option'],
            'min' => $args['min'],
            'max' => $args['max'],
            'inc' => (!empty($args['inc'])) ? $args['inc'] : 1,
            'conditions' => $args['conditions'],
            'description' => (isset($args['description'])) ? $args['description'] : false
        ]);
    }

    /**
     * Registers an option with a dropdown/select input
     * @param $option_name
     * @param $options
     * @param $title
     * @param $settings_slug
     * @param null $description
     * @param null $conditions
     */
    protected function registerSelectSetting($option_name, $options, $title, $settings_slug, $description=null, $conditions=null) {
        add_settings_field($option_name,
            $title,
            [$this,'renderSelectSetting'],
            $this->options_page,
            $settings_slug,
            ['option'=>$option_name,'options'=>$options,'description'=>$description, 'conditions'=>$conditions]);
    }

    /**
     * Renders the select
     * @param $args
     */
    public function renderSelectSetting($args) {
        $options = $args['options'];
        if (!is_array($options)) {
            $options = $this->$options();
        }

        echo View::render_view('base/fields/select.php',[
            'value' => Environment::Option($args['option']),
            'name' => $args['option'],
            'options' => $options,
            'conditions' => $args['conditions'],
            'description' => (isset($args['description'])) ? $args['description'] : false
        ]);
    }

    /**
     * Registers an option with a dropdown/select input
     * @param $option_name
     * @param $options
     * @param $title
     * @param $settings_slug
     * @param null $description
     * @param null $conditions
     */
    protected function registerDynamicSelectSetting($option_name, $options, $title, $settings_slug, $description=null, $conditions=null) {
        add_settings_field($option_name,
            $title,
            [$this,'renderSelectSetting'],
            $this->options_page,
            $settings_slug,
            ['option'=>$option_name,'options'=>$options,'description'=>$description, 'conditions'=>$conditions]);
    }

    /**
     * Renders the select
     * @param $args
     */
    public function renderDynamicSelectSetting($args) {
        $options = $args['options'];
        if (!is_array($options)) {
            $options = $this->$options();
        }

        echo View::render_view('base/fields/select.php',[
            'value' => Environment::Option($args['option']),
            'name' => $args['option'],
            'options' => $options,
            'conditions' => $args['conditions'],
            'description' => (isset($args['description'])) ? $args['description'] : false
        ]);
    }
}
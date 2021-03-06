<?php

namespace rokorolov\parus\admin\theme\widgets\statusaction;

use rokorolov\helpers\Html;
use rokorolov\parus\admin\theme\widgets\statusaction\StatusActionAsset;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\bootstrap\Button;
use yii\bootstrap\Dropdown;
use yii\base\InvalidConfigException;
use yii\base\Widget;

/**
 * StatusAction is a widget for manage the statuses
 *
 * @author Roman Korolov <rokorolov@gmail.com>
 */
class StatusAction extends Widget
{
    /**
     * @var integer current status
     */
    public $status;

    /**
     * @var mixed $key the key associated with the data model
     */
    public $key;

    /**
     * @var string the ID of the controller.
     */
    public $controller;

    /**
     * @var string action of the controller.
     */
    public $action = 'newstatus';

    /**
     * @var array list of items.
     *
     * $buttons =  [
     *       1 => [
     *           'label' => Publish,
     *           'icon' => 'check fa-fw',
     *           'type' => success,
     *           'changeTo' => 2,
     *       ],
     *       2 => [
     *           'label' => Unpublish,
     *           'icon' => 'times fa-fw',
     *           'type' => warning,
     *           'changeTo' => 1,
     *       ], ...
     *
     *
     */
    public $buttons = [];
    
    /**
     * @var array the HTML attributes for the widget container tag.
     */
    public $options = [];

    /**
     * @var array the HTML attributes for the container tag.
     */
    public $containerOptions = [];

    /**
     * @var string button size.
     */
    public $size = Html::SIZE_TINY;

    /**
     * @var boolean whether the labels for menu items should be HTML-encoded.
     */
    public $encodeLabels = true;

    /**
     * @var string pjax conteiner id.
     */
    public $enable = true;

    /**
     * @var string pjax conteiner id.
     */
    public $pjaxContainer;

    /**
     * Initializes the widget.
     */
    public function init()
    {
        parent::init();

        if (empty($this->buttons)) {
            throw new InvalidConfigException("You must setup the 'buttons' array property.");
        }

        if ($this->status === null) {
            throw new InvalidConfigException("You must setup the 'status' property.");
        }

        if ($this->key === null) {
            throw new InvalidConfigException("You must setup the 'key' property.");
        }
        
        if ($this->enable) {
            Html::addCssClass($this->options, 'newstatus');
        } else {
            Html::addCssClass($this->options, 'disabled');
        }

      //  self::registerTranslation();

        Html::addCssClass($this->containerOptions, 'btn-group status-actions');
    }

    /**
     * Renders the widget.
     *
     * @return string the created button dropdowns.
     */
    public function run()
    {
        $this->pjaxContainer === null ?: $this->registerScripts();

        $tag = ArrayHelper::remove($this->containerOptions, 'tag', 'div');

        return implode("\n", [
            Html::beginTag($tag, $this->containerOptions),
            $this->renderButtonDropdown(),
            $this->renderDropdown(),
            Html::endTag('div'),
        ]);
    }

    /**
     * Creates a URL for the given action and model.
     * This method is called for each status action.
     *
     * @param mixed $key the key associated with the data model
     * @param mixed $status the current row status
     * @return string the created URL
     */
    protected function createUrl($key, $status)
    {
        return [$this->controller ? $this->controller . '/' . $this->action : $this->action, 'id' => $key, 'status' => $status];
    }

    /**
     * Generates the split button.
     *
     * @return string the rendering result.
     */
    protected function renderButtonDropdown()
    {
        $button = ArrayHelper::remove($this->buttons, $this->status);
        $url = $this->createUrl($this->key, $button['changeTo']);
        $options = $this->options;

        Html::addCssClass($options, 'btn btn-' . $this->size . ' btn-default');
        $options['title'] = ArrayHelper::getValue($this->buttons, $button['changeTo'] . '.label');
        $options['data-pjax'] = '0';
        $options['href'] = Url::to($url);

        return Button::widget([
            'label' => Html::icon($button['icon'] . ' text-' . $button['type']),
            'encodeLabel' => false,
            'options' => $options,
            'tagName' => 'a'
        ]);
    }

    /**
     * Generates the dropdown menu.
     *
     * @return string the rendering result.
     */
    protected function renderDropdown()
    {
        $buttons = [];
        foreach($this->buttons as $key => $button) {
            $options = $this->options;
            unset($options['id']);
            $url = $this->createUrl($this->key, $key);
            $label = $this->encodeLabels ? Html::encode($button['label']) : $button['label'];
            $buttons[] = [
                'label' => isset($button['icon']) ? Html::icon($button['icon'], ['class' => 'text-' . $button['type']]) . ' ' . $label : $label,
                'url' => $url,
                'linkOptions' => $options,
            ];
        }

        if (count($buttons) <= 1) {
            return '';
        }

        $splitButton = Button::widget([
            'label' => '<span class="caret"></span>',
            'encodeLabel' => false,
            'options' => ['data-toggle' => 'dropdown', 'aria-haspopup' => 'true', 'class' => !$this->enable ? 'btn-default dropdown-toggle btn-xs disabled' : 'btn-default dropdown-toggle btn-xs'],
        ]);

        return $splitButton . "\n" . Dropdown::widget([
            'items' => $buttons,
            'encodeLabels' => false,
            'options' => [
                'class' => 'pull-right'
            ]
        ]);
    }

    /**
     * Register Client Scripts
     */
    public function registerScripts()
    {
        $view = $this->getView();
        StatusActionAsset::register($view);
        $view->registerJs("$('.newstatus').click(function (event) { event.preventDefault(); statusaction.change(this, '$this->pjaxContainer');});");
    }
}

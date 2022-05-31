<?php
/**
 * form.php
 *
 * PHP version 7.4+
 *
 * @author Philippe Gaultier <pgaultier@redcat.io>
 * @copyright 2010-2022 Redcat
 * @license https://www.redcat.io/license license
 * @version XXX
 * @link https://www.redcat.io
 * @package blackcube\plugins\graphql\admin\views\configure
 *
 * @var $configureModel \blackcube\plugins\graphql\models\ConfigureModel
 * @var $boId string
 * @var $this \yii\web\View
 */

use blackcube\admin\helpers\Html;
use blackcube\admin\helpers\BlackcubeHtml;
use blackcube\admin\helpers\Heroicons;
use yii\helpers\Url;
use blackcube\admin\helpers\Aurelia;
use yii\helpers\ArrayHelper;
use blackcube\plugins\graphql\Plugin;

?>
<main class="application-content">
    <?php echo Html::beginForm('', 'post', [
        'class' => 'element-form-wrapper',
    ]); ?>
    <div class="page-header">
        <?php echo Html::beginTag('a', [
            'class' => 'text-white',
            'href' => Url::to([$boId.'/plugin'])
        ]); ?>
        <?php echo Heroicons::svg('solid/chevron-left', ['class' => 'h-7 w-7']); ?>
        <?php echo Html::endTag('a'); ?>
        <h3 class="page-header-title"><?php echo Plugin::t('gql', 'Graphql'); ?></h3>
    </div>
    <div class="px-6 pb-6">
        <div class="element-form-bloc">

            <div class="element-form-bloc-stacked">
                <?php echo BlackcubeHtml::activeTextInput($configureModel, 'name', []); ?>
            </div>
        </div>
    </div>
    <div class="px-6 pb-6">

        <div class="element-form-buttons">
            <?php echo Html::beginTag('a', [
                'class' => 'element-form-buttons-button',
                'href' => Url::to([$boId.'/plugin'])
            ]); ?>
            <?php echo Heroicons::svg('solid/x', ['class' => 'element-form-buttons-button-icon']); ?>
            <?php echo Plugin::t('common', 'Cancel'); ?>
            <?php echo Html::endTag('a'); ?>
            <?php echo Html::beginTag('button', [
                'class' => 'element-form-buttons-button action',
                'type' => 'submit'
            ]); ?>
            <?php echo Heroicons::svg('solid/check', ['class' => 'element-form-buttons-button-icon']); ?>
            <?php echo Plugin::t('common', 'Save'); ?>
            <?php echo Html::endTag('button'); ?>
        </div>
    </div>

    <?php echo Html::endForm(); ?>
</main>

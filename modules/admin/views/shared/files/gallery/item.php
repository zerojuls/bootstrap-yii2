<?php
use yii\helpers\Html;
?>
<li>
  <a href="<?= $file->getStorage()->path()?>" target="_blank">
    <img src="<?= $model->thumb('gallery', '80x80', $file->getStorage()->path())?>">
  </a>
  <a class="btn btn-lg"><span class="glyphicon glyphicon-remove remove-item" data-remove-item="li"></span></a>
  <?= Html::activeTextInput($model, $attribute.'[' . $file->id .']', [
      'class' => 'form-control',
      'value' => $file->title
  ]) ?>
</li>

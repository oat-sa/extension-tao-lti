<?php
use oat\tao\helpers\Template;
?>
<script>
window.open('<?=get_data('url')?>');
</script>
<div class="main-container">
	<div id="form-title" class="ui-widget-header ui-corner-top ui-state-default">
		<?=__('Third party cookies are not supported by your browser')?>
	</div>
	<div id="form-container" class="ui-widget-content ui-corner-bottom">
		click <a href="<?=get_data('url')?>" target="_blank">here</a> for magic
	</div>
</div>
<?php
Template::inc('footer.tpl','tao');
?>

<div class="data-container-wrapper flex-container-full">
    <div class="grid-row">
        <div class="col-12">
            <h1><?= __('LTI Key Pair'); ?></h1>
        </div>
    </div>
</div>

<div class="content-block">
    <pre id="key-pair-content"></pre>
</div>

<button id="generate" class="btn-info">Generate</button>

<script type="text/javascript">
    require(['jquery'], function($) {

        <?php if(has_data('lti-key-pair')): ?>
            let ltiKeyPair = JSON.stringify(JSON.parse('<?= get_data("lti-key-pair"); ?>'), null, 2);
            $("#key-pair-content").text(ltiKeyPair);
        <?php endif; ?>

        $("#generate").click(function() {
            let url = '<?= get_data("lti-key-pair-generate-url"); ?>';
            $.ajax({
                url: url,
                type: "POST",
                success: function(response) {
                    location.reload();
                }
            });
        });

    });
</script>

<div class="data-container-wrapper flex-container-full">
    <div class="grid-row">
        <div class="col-12">
            <h1><?= __('LTI Key Chain'); ?></h1>
        </div>
    </div>
</div>

<div class="content-block">
    <pre id="key-chain-content"></pre>
</div>

<button id="generate" class="btn-info">Generate</button>

<script type="text/javascript">
    require(['jquery'], function($) {

        <?php if(has_data('lti-key-chain')): ?>
            let ltiKeyChain = JSON.stringify(JSON.parse('<?= get_data("lti-key-chain"); ?>'), null, 2);
            $("#key-chain-content").text(ltiKeyChain);
        <?php endif; ?>

        $("#generate").click(function() {
            let url = '<?= get_data("lti-key-chain-generate-url"); ?>';
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

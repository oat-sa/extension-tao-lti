<div class="data-container-wrapper flex-container-full">
    <div class="grid-row">
        <div class="col-12">
            <h1><?= __('LTI Key Chain'); ?></h1>
        </div>
    </div>
    <pre id="key-chain-content"></pre>
    <hr/>
    <div class="grid-row">
        <div class="col-12">
            <h1><?= __('LTI JWKS'); ?></h1>
        </div>
    </div>
    <pre id="lti-jwks-content"></pre>
</div>

<button id="generate" class="btn-info">Generate</button>

<script type="text/javascript">
    require(['jquery'], function($) {

        <?php if(has_data('lti-key-chains')): ?>
            let ltiKeyChain = JSON.stringify(<?= get_data("lti-key-chains"); ?>, null, 2).replace(/\\n/g, '');
            $("#key-chain-content").text(ltiKeyChain);
        <?php endif; ?>

        <?php if(has_data('lti-jwks')): ?>
            let ltiJwks = JSON.stringify(<?= get_data("lti-jwks"); ?>, null, 2).replace(/\\n/g, '');
            $("#lti-jwks-content").text(ltiJwks);
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

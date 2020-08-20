<div class="data-container-wrapper flex-container-full">
    <div class="grid-row">
        <div class="col-12">
            <h1><?= __('Jwks settings'); ?></h1>
        </div>
    </div>
</div>

<div class="content-block">
    <pre id="jwks-content"></pre>
</div>

<button id="generate" class="btn-info">Generate</button>

<script type="text/javascript">
    require(['jquery'], function($) {

        <?php if(has_data('jwks-key')): ?>
            let jwksKey = JSON.stringify(JSON.parse('<?= get_data("jwks-key"); ?>'), null, 2);
            $("#jwks-content").text(jwksKey);
        <?php endif; ?>

        $("#generate").click(function() {
            let url = '<?= get_data("jwks-generate-url"); ?>';
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

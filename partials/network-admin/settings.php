<style>
    .mabs_clear_cache_message {color:green}
</style>

<script>
    function mabs_clear_cache(button)
    {
        jQuery(button).attr('disabled', 'disabled');
        jQuery.get('<?= admin_url('admin-ajax.php?action=clear_mabs_cache') ?>', function(response) {
            jQuery('span.mabs_clear_cache_message').html(response);
            jQuery(button).removeAttr('disabled');
        });
    }
</script>

<form action="<?php echo admin_url('admin-post.php?action=update_mabs_settings'); ?>" method="post">
    <?php wp_nonce_field('mabs_nonce'); ?>

    <p>MABS caches the blogs list for all users to speed up admin bar generation. If some users aren't seeing the correct list of sites they have access to, use the button below to clear the MABS cache. </p>

    <input type="button" value="Clear Cache" onclick="return mabs_clear_cache(this);" />
    <span class="mabs_clear_cache_message"></span>

</form>
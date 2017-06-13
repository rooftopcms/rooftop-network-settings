<div class="wrap">
    <h1>Add new GTM code</h1>
    <p>
        This will be included on admin pages for each site.
    </p>

    <form action="?page=rooftop-settings-gtm" method="post">
        <table class="form-table">
            <tr>
                <th scope="row">
                    GTM Key
                </th>

                <td>
                    <input type="text" name="gtm_key" placeholder="GTM Key" value="<?php echo (isset($gtm_key) ? $gtm_key : '') ?>"/>
                </td>
            </tr>
        </table>

        <?php wp_nonce_field( 'rooftop-network-settings-add-key', 'gtm-field-token' ); ?>

        <p class="submit">
            <input type="submit" value="Save GTM Key" class="button button-primary" />
        </p>

    </form>
</div>

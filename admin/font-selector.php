<?php
if (!defined('ABSPATH')) exit;
?>
<div class="udnpc-field-row">
    <label for="udnpc-font-family"><?php esc_html_e('Photo card font', 'news-photocard-pro'); ?></label>
    <select id="udnpc-font-family" name="font_family" aria-describedby="udnpc-font-help">
        <?php foreach ($fonts as $family => $label): ?>
        <option value="<?php echo esc_attr($family); ?>" <?php selected($font, $family); ?>><?php echo esc_html($label); ?></option>
        <?php endforeach; ?>
    </select>
    <p id="udnpc-font-help" class="udnpc-help"><?php esc_html_e('Applies to all designs and PNG downloads. Changes appear in the preview immediately; save to apply them to downloads. Solaiman Lipi is the default.', 'news-photocard-pro'); ?></p>
</div>

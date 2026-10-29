<?php

function kreativ_get_tool_catalogue() {
    return array(
        'kreativ-font-identifier' => array(
            'title' => 'Font Identifier',
            'task' => 'Identify a font',
            'summary' => 'Upload a lettering image and explore possible font matches.',
            'icon' => 'fa-magnifying-glass',
            'action' => 'Identify a font',
        ),
        'kreativ-font-pairing-tools' => array(
            'title' => 'Font Pairing Tools',
            'task' => 'Find fonts that work together',
            'summary' => 'Find pairings, explore similar fonts, or compare two typefaces for your project.',
            'icon' => 'fa-object-group',
            'action' => 'Explore font pairings',
            'kind' => 'pairing',
            'input' => 'Start with a font you know',
            'hint' => 'Add a design context, such as an editorial website or a book cover, for more useful suggestions.',
            'note' => 'Suggestions are a starting point. Check the actual typefaces and their licenses before choosing.',
        ),
        'kreativ-font-name-generator' => array(
            'title' => 'Font Name Generator',
            'task' => 'Name a type family',
            'summary' => 'Turn a type style or creative direction into font-family name ideas. Save favorites and explore family variants.',
            'icon' => 'fa-pen-nib',
            'action' => 'Create name ideas',
            'kind' => 'naming',
            'input' => 'Describe the character of your type',
            'hint' => 'Try a quick preset or describe the mood, shape, and intended use. Choose one or two words for the name format.',
            'note' => 'Generated names may already be in use. Check availability before publishing a font or brand.',
        ),
        'fancy-text-generator' => array(
            'title' => 'Fancy Text Generator',
            'task' => 'Style text for sharing',
            'summary' => 'Copy Unicode lettering or download a styled PNG for a bio, caption, or graphic.',
            'icon' => 'fa-wand-magic-sparkles',
            'action' => 'Create fancy text',
        ),
    );
}

function kreativ_get_creative_tool_config( $post = null ) {
    $post = get_post( $post );
    if ( ! $post instanceof WP_Post ) {
        return null;
    }
    $tools = kreativ_get_tool_catalogue();
    $config = $tools[ $post->post_name ?? '' ] ?? null;
    return ! empty( $config['kind'] ) ? $config : null;
}

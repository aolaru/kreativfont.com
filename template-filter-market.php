<?php
/*
Template Name: Kreativ Market Home
*/
get_header();

/**
 * CATEGORY LABELS + ICONS
 */
$kreativ_category_labels = [
    'fonts'            => 'Fonts',
    'templates-themes' => 'Templates',
    'graphics'         => 'Graphics',
    'photos'           => 'Photos',
    'videos'           => 'Videos',
    'sounds'           => 'Sounds',
    'free'             => 'Freebies',
];

$kreativ_fa_icons = [
    'fonts'            => 'fa-solid fa-font',
    'templates-themes' => 'fa-solid fa-layer-group',
    'graphics'         => 'fa-solid fa-pen-nib',
    'photos'           => 'fa-solid fa-camera',
    'videos'           => 'fa-solid fa-film',
    'sounds'           => 'fa-solid fa-music',
    'free'             => 'fa-solid fa-gift',
];

/**
 * Returns category slug + label for primary badge
 */
function kf_get_primary_category_badge( $post_id, $labels ) {
    $terms = get_the_terms( $post_id, 'category' );
    if ( ! $terms || is_wp_error( $terms ) ) return [ null, null ];
    foreach ( $terms as $term ) {
        if ( isset( $labels[ $term->slug ] ) ) {
            return [ $term->slug, $labels[ $term->slug ] ];
        }
    }
    return [ null, null ];
}

/**
 * “NEW” badge for posts younger than 7 days
 */
function kf_is_new_post( $post_id ) {
    return ( time() - get_post_time( 'U', true, $post_id ) ) <= 7 * DAY_IN_SECONDS;
}

?>

<style>

/* ---------------------------------------------------------
   GENERAL STYLES
--------------------------------------------------------- */
:root {
    --kf-section-pad: 4rem;
    --kf-gradient: linear-gradient(180deg, #fafbff 0%, #f4f6ff 100%);
}
.kreativ-hero {
    padding: 3rem 1rem;
    text-align: center;
    background: linear-gradient(135deg, #eef1ff 0%, #fafbff 100%);
    border-radius: 22px;
    margin-bottom: 2.5rem;
}

/* ---------------------------------------------------------
   HERO TOOL SHORTCUTS
--------------------------------------------------------- */
.kreativ-hero-tools {
    display: flex;
    justify-content: center;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 1.5rem;
}

.kreativ-hero-tool-card {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.75rem 1.3rem;
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e4ea;
    text-decoration: none;
    color: #1a1a1a;
    transition: 0.25s ease;
    font-weight: 600;
}

.kreativ-hero-tool-card i {
    font-size: 1.2rem;
    color: #4A4AFF;
}

.kreativ-hero-tool-card:hover {
    box-shadow: 0px 5px 18px rgba(0,0,0,0.12);
    transform: translateY(-3px);
    border-color: #4A4AFF;
}

@media(max-width: 767px) {
    .kreativ-hero-tool-card {
        width: 100%;
        justify-content: center;
    }
}

/* ---------------------------------------------------------
   SECTION WRAPPER
--------------------------------------------------------- */
.kreativ-section {
    padding: 1rem 0;
    background: var(--kf-gradient);
    border-radius: 22px;
    margin-bottom: 3rem;
}

/* ---------------------------------------------------------
   SECTION HEADERS
--------------------------------------------------------- */
.kreativ-section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.2rem;
}

.kreativ-section-title {
    font-size: 1.8rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.6rem;
}

.kreativ-section-title i {
    font-size: 1.8rem;
    color: #4A4AFF;
}

.kf-view-all {
    font-size: 1.2rem;
    padding-right: 4px;
    color: #4A4AFF;
    text-decoration: none;
    transition: 0.2s ease;
}

.kf-view-all:hover {
    color: #2d2def;
    transform: translateX(3px);
}

/* ---------------------------------------------------------
   SECTION GRADIENTS MATCHING BADGE COLORS
--------------------------------------------------------- */

/* Fonts — very light blue */
.kreativ-section-fonts {
    background: linear-gradient(135deg, #f8f9ff 0%, #fdfdff 100%) !important;
}

/* Templates — very light teal */
.kreativ-section-templates-themes {
    background: linear-gradient(135deg, #f5fcfc 0%, #fcfefe 100%) !important;
}

/* Graphics — very light peach */
.kreativ-section-graphics {
    background: linear-gradient(135deg, #fff9f6 0%, #fffdfb 100%) !important;
}

/* Photos — very light gold */
.kreativ-section-photos {
    background: linear-gradient(135deg, #fffdf3 0%, #fffef9 100%) !important;
}

/* Videos — very light coral */
.kreativ-section-videos {
    background: linear-gradient(135deg, #fff6f6 0%, #fffafa 100%) !important;
}

/* Sounds — very light lavender */
.kreativ-section-sounds {
    background: linear-gradient(135deg, #fcf7ff 0%, #fefbff 100%) !important;
}

/* Freebies — green */
.kreativ-section-free {
    background: linear-gradient(135deg, #e2f7e8 0%, #f3fff6 100%) !important;
}	
	
/* ---------------------------------------------------------
   CARDS
--------------------------------------------------------- */
.kreativ-font-card {
    background: #fff;
    padding: 1rem;
    border-radius: 14px;
    margin-bottom: 1rem;
    border: 1px solid #e2e4ea;
    transition: 0.25s ease;
}

.kreativ-font-card:hover {
    box-shadow: 0px 5px 22px rgba(0,0,0,0.12);
    transform: translateY(-4px);
	border-color: #4A4AFF;
}

.kreativ-font-card a {
    text-decoration: none;
    color: inherit;
    display: block;
}

.kreativ-card-media {
    position: relative;
    overflow: hidden;
    border-radius: 10px;
}

.kreativ-card-media img {
    width: 100%;
    transition: transform 0.3s ease;
}

.kreativ-card-media:hover img {
    transform: scale(1.06);
}
	
.col-md-3, .col-sm-6 {
	padding-left:10px;
	padding-right:10px; 	
}
/* Category badges */
.kf-badge {
    position: absolute;
    top: 0.75rem;
    left: 0.75rem;
    padding: 0.2rem 0.6rem;
    border-radius: 999px;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: white;
    z-index: 2;
}

.kf-badge-fonts            { background: #4A4AFF; }
.kf-badge-templates-themes { background: #00A7B5; }
.kf-badge-graphics         { background: #FF7A59; }
.kf-badge-photos           { background: #F4B400; }
.kf-badge-videos           { background: #E53935; }
.kf-badge-sounds           { background: #8E24AA; }
.kf-badge-free             { background: #43A047; }

/* NEW badge */
.kf-badge-new {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    padding: 0.2rem 0.5rem;
    border-radius: 999px;
    background: #ff3366;
    color: #fff;
    font-size: 0.7rem;
    font-weight: bold;
    letter-spacing: 0.03em;
    z-index: 2;
}

/* Card titles */
.kreativ-font-card h3 {
    font-size: 1rem;
    margin-top: 0.85rem;
    margin-bottom: 0;
    font-weight: 600;
}

/* Responsive */
@media(max-width:767px) {
    .kreativ-section-title { font-size: 1.4rem; }
}
</style>



<!-- =====================================================
     HERO SECTION WITH TOOLS
===================================================== -->
<div class="kreativ-hero container">

    <p style="font-size:1.2rem; margin-bottom:2rem;">
        Fonts, Templates, Graphics, Photos, Videos & Sounds — updated daily.
    </p>

    <div class="kreativ-hero-tools">
		
		<span class="kreativ-hero-tool-card">Tools: </span>
		

        <a href="/tools/kreativ-font-pairing-tools class="kreativ-hero-tool-card">
            <i class="fa-solid fa-search"></i>
            <span>Font Pairing Tools</span>
									 
        </a>		

        <a href="/tools/kreativ-font-identifier" class="kreativ-hero-tool-card">
            <i class="fa-solid fa-search"></i>
            <span>Font Identifier</span>
        </a>

        <a href="/tools/fancy-text-generator" class="kreativ-hero-tool-card">
            <i class="fa-solid fa-wand-magic-sparkles"></i>
            <span>Fancy Text Generator</span>
        </a>

        <a href="/tools/kreativ-font-name-generator" class="kreativ-hero-tool-card">
            <i class="fa-solid fa-lightbulb"></i>
            <span>Font Name Generator</span>
        </a>

    </div>

</div>



<!-- =====================================================
     LATEST BY CATEGORY
===================================================== -->
<?php
$home_sections = [
    'fonts'            => 'Latest Fonts',
    'templates-themes' => 'Latest Templates',
    'graphics'         => 'Latest Graphics',
    'photos'           => 'Latest Photos',
    'videos'           => 'Latest Videos',
    'sounds'           => 'Latest Sounds',
];

foreach ( $home_sections as $slug => $title ) :
    $icon = $kreativ_fa_icons[$slug];
?>

<div class="container kreativ-section kreativ-section-<?php echo $slug; ?>">	

    <div class="kreativ-section-header">
        <h2 class="kreativ-section-title">
            <i class="<?php echo $icon; ?>"></i>
            <?php echo esc_html($title); ?>
        </h2>
        <a href="/category/<?php echo $slug; ?>" class="kf-view-all">View All &rsaquo;</a>
    </div>

    <div class="row">
        <?php
        $query = new WP_Query([
            'posts_per_page' => 8,
            'tax_query' => [[
                'taxonomy' => 'category',
                'field'    => 'slug',
                'terms'    => [$slug],
            ]],
        ]);

        if ($query->have_posts()):
            while ($query->have_posts()):
                $query->the_post();

                $thumb = wp_get_attachment_image_src(
                    get_post_thumbnail_id(), 'medium'
                )[0] ?: get_template_directory_uri() . "/img/default-thumb.png";

                $is_new = kf_is_new_post(get_the_ID());
        ?>

        <div class="col-md-3 col-sm-6 kreativ-card-animate">
            <div class="kreativ-font-card">
                <a href="<?php the_permalink(); ?>">
                    <div class="kreativ-card-media">

                        <span class="kf-badge kf-badge-<?php echo $slug; ?>">
                            <?php echo $kreativ_category_labels[$slug]; ?>
                        </span>

                        <?php if ($is_new): ?>
                        <span class="kf-badge-new">NEW</span>
                        <?php endif; ?>

                        <img class="lazyload"
                             loading="lazy"
                             data-src="<?php echo esc_url($thumb); ?>"
                             src="<?php echo get_template_directory_uri(); ?>/img/loading.gif" />
                    </div>
                    <h3><?php the_title(); ?></h3>
                </a>
            </div>
        </div>

        <?php endwhile; endif; wp_reset_postdata(); ?>
    </div>
</div>
<?php endforeach; ?>



<!-- =====================================================
     FREEBIES
===================================================== -->
<div class="container kreativ-section kreativ-section-free">

    <div class="kreativ-section-header">
        <h2 class="kreativ-section-title">
            <i class="fa-solid fa-gift"></i>
            Free Creative Resources
        </h2>
        <a href="/category/free" class="kf-view-all">View All &rsaquo;</a>
    </div>

    <div class="row">
        <?php
        $free = new WP_Query([
            'category_name' => 'free',
            'posts_per_page'=> 8
        ]);

        if ($free->have_posts()):
            while ($free->have_posts()):
                $free->the_post();

                $thumb = wp_get_attachment_image_src(
                    get_post_thumbnail_id(), 'medium'
                )[0] ?: get_template_directory_uri() . "/img/default-thumb.png";

                $is_new = kf_is_new_post(get_the_ID());
        ?>

        <div class="col-md-3 col-sm-6 kreativ-card-animate">
            <div class="kreativ-font-card">
                <a href="<?php the_permalink(); ?>">
                    <div class="kreativ-card-media">

                        <span class="kf-badge kf-badge-free">Free</span>

                        <?php if ($is_new): ?>
                        <span class="kf-badge-new">NEW</span>
                        <?php endif; ?>

                        <img class="lazyload"
                             loading="lazy"
                             data-src="<?php echo esc_url($thumb); ?>"
                             src="<?php echo get_template_directory_uri(); ?>/img/loading.gif" />
                    </div>
                    <h3><?php the_title(); ?></h3>
                </a>
            </div>
        </div>

        <?php endwhile; endif; wp_reset_postdata(); ?>
    </div>
</div>

<?php get_footer(); ?>

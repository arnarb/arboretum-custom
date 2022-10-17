<?php
use Arboretum\Repositories\DirectorRepository;


/**
 * Create a shortcode for director's lectures
 */
function directors_reports($atts) {
    $context = Timber::context();

    $page = Timber::get_post();
    $context['page'] = $page;

    $directorRepo = new DirectorRepository();
    $context['directors'] = $directorRepo->getDirectors()->get();

    ob_start();
    ?>
        <div class="cleanfix">
    <?php
        Timber::render('components/directors-reports-accordion.twig', $context);
    ?>
        </div>
    <?php
    return ob_get_clean();
}
add_shortcode('directors_reports', 'directors_reports');


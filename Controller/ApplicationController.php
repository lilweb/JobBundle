<?php
/**
 * User: michiel
 * Date: 18/06/13
 * Time: 18:26
 */
namespace Lilweb\JobBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Permets d'afficher l'application principale.
 */
class ApplicationController extends Controller
{
    /**
     * Affiche l'application
     *
     * @Template
     */
    public function indexAction()
    {
        return array();
    }
}
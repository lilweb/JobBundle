<?php
/**
 * User: michiel
 * Date: 18/06/13
 * Time: 17:00
 */
namespace Lilweb\JobBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controlleur pour les accès API JSON pour l'administration.
 */
class JsonAPIController extends Controller
{
    /**
     * Retourne la liste des jobs/tasks pour la journées
     *
     * @param null $year
     * @param null $month
     * @param null $day
     *
     * @return Response
     */
    public function getJobsForDayAction($year = null, $month = null, $day = null)
    {
        // Récupération de la liste des taches
        $jobs = $this->getDoctrine()->getRepository('LilwebJobBundle:JobInfo')->findByDay($year, $month, $day);

        // Mise en page des résultats.
        $content = $this->renderView(
            'LilwebJobBundle:JsonAPI:jobs.json.twig',
            array(
                'jobs' => $jobs
            )
        );

        return new Response($content, 200, array('Content-Type' => 'application/json'));
    }

    /**
     * Retourne la liste des taches pour un job donné
     */
    public function getTasksForJobAction($id)
    {
        $tasks = $this->getDoctrine()->getRepository('LilwebJobBundle:TaskInfo')->findBy(
            array('jobInfo' => $id),
            array('id' => 'asc')
        );

        $content = $this->renderView(
            'LilwebJobBundle:JsonAPI:tasks.json.twig',
            array(
                'tasks' => $tasks
            )
        );

        return new Response($content, 200, array('Content-Type' => 'application/json'));
    }
}
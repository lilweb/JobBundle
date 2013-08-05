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
     * @param $annee L'Année
     * @param $mois La mois
     * @param $jour La date du jour
     *
     * @return Response
     */
    public function getJobsForDayAction($annee, $mois, $jour)
    {
        // Récupération de la liste des taches
        $jobs = $this->getDoctrine()->getRepository('LilwebJobBundle:JobInfo')->findForDay($annee, $mois, $jour);
        $columns = $this->container->getParameter('lilweb_job_bundle.columns');

        // Mise en page des résultats.
        $content = $this->renderView('LilwebJobBundle:JsonAPI:jobs.json.twig', array(
            'jobs'   => $jobs,
            'columns' => $columns
        ));

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
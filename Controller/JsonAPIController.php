<?php
/**
 * User: michiel
 * Date: 18/06/13
 * Time: 17:00
 */
namespace Lilweb\JobBundle\Controller;

use Lilweb\JobBundle\Entity\JobInfo;
use Lilweb\JobBundle\Entity\TaskInfo;

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
     *
     * @param $id L'identifiant du job dont il faut afficher les taches.
     *
     * @return Response
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

    /**
     * Retourne le job en question.
     *
     * @param $jobInfo JobInfo Le job à afficher.
     *
     * @return Response
     */
    public function getJobAction(JobInfo $jobInfo)
    {
        $columns = $this->container->getParameter('lilweb_job_bundle.columns');
        $content = $this->renderView(
            'LilwebJobBundle:JsonAPI:partial/job.json.twig',
            array(
                'job'     => $jobInfo,
                'columns' => $columns
            )
        );

        return new Response($content, 200, array('Content-Type' => 'application/json'));
    }

    /**
     * Retourne les informations de la tache.
     *
     * @param $taskInfo TaskInfo La tache à afficher.
     *
     * @return Response
     */
    public function getTaskAction(TaskInfo $taskInfo)
    {
        $content = $this->renderView('LilwebJobBundle:JsonAPI:partial/task.json.twig', array('task' => $taskInfo));

        return new Response($content, 200, array('Content-Type' => 'application/json'));
    }

    /**
     * Redémarre le job en question
     *
     * @param $jobInfo JobInfo Le job à redémarrer
     *
     * @return Response
     */
    public function restartJobAction(JobInfo $jobInfo)
    {
        foreach ($jobInfo->getTaskInfos() as $taskInfo) {
            $taskInfo->setStatus(TaskInfo::TASK_WAITING);
            $taskInfo->setInfoMsg('');
        }

        $this->getDoctrine()->getManager()->flush();

        return new Response('', 200, array('Content-Type' => 'application/json'));
    }

    /**
     * Permets d'abandonner un job.
     *
     * @param $job JobInfo Le job qu'il faut abandonné.
     *
     * @return Response
     */
    public function abandonJobAction(JobInfo $job)
    {
        foreach ($job->getTaskInfos() as $taskInfo) {
            $taskInfo->setStatus(TaskInfo::TASK_DROPPED);
        }

        $this->getDoctrine()->getManager()->flush();

        return new Response('', 200, array('Content-Type' => 'application/json'));
    }

    /**
     * Relance cette tache ainsi que les suivantes.
     *
     * @param TaskInfo $taskInfo La tache a relancer.
     *
     * @return Response
     */
    public function restartTaskAction(TaskInfo $taskInfo)
    {
        $jobInfo = $taskInfo->getJobInfo();
        foreach ($jobInfo->getTaskInfos() as $task) {
            if ($task->getOrdre() >= $taskInfo->getOrdre()) {
                $task->setStatus(TaskInfo::TASK_WAITING);
                $task->setInfoMsg('');
            }
        }

        $this->getDoctrine()->getManager()->flush();

        return new Response('', 200, array('Content-Type' => 'application/json'));
    }

    /**
     * Permets de skipper une tache
     *
     * @param TaskInfo $taskInfo La tache à passer.
     *
     * @return Response
     */
    public function skipTaskAction(TaskInfo $taskInfo)
    {
        $taskInfo->setStatus(TaskInfo::TASK_SKIPPED);
        $this->getDoctrine()->getManager()->flush();

        return new Response('', 200, array('Content-Type' => 'application/json'));
    }
}

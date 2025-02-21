<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

class TaskController extends AbstractController
{
    #[Route('/task', name: 'list_task', methods: 'GET')]
    public function index(TaskRepository $taskRepository): JsonResponse
    {
        $taskList = $taskRepository->findAll();

        $data = [];
        if (!empty($taskList)) {
            foreach ($taskList as $task) {
                $data[] = [
                    'id' => $task->getId(),
                    'name' => $task->getName(),
                    'description' => $task->getDescription(),
                ];
            }
        }

        return $this->json($data);
    }

    #[Route('/task/{id}', name: 'get_by_id_task', methods: 'GET')]
    public function getById(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $task = $entityManager->getRepository(Task::class)->find($id);

        if (!$task) {
            throw $this->createNotFoundException(
                'No task found for id ' . $id
            );
        }

        $data = [
            'id' => $task->getId(),
            'name' => $task->getName(),
            'description' => $task->getDescription(),
        ];

        return $this->json($data);
    }

    #[Route('/task', name: 'create_task', methods: 'POST')]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $parameters = json_decode($request->getContent(), true);

        $task = new Task();
        $task->setName($parameters['name']);
        $task->setDescription($parameters['description']);

        $entityManager->persist($task);
        $entityManager->flush();

        return $this->json([
            'id' => $task->getId(),
        ]);
    }

    #[Route('/task/{id}', name: 'edit_task', methods: 'PATCH')]
    public function edit(Request $request, int $id, EntityManagerInterface $entityManager): Response
    {
        $task = $entityManager->getRepository(Task::class)->find($id);

        if (!$task) {
            throw $this->createNotFoundException(
                'No task found for id ' . $id
            );
        }

        $parameters = json_decode($request->getContent(), true);

        if (array_key_exists('name', $parameters)) {
            if (empty($parameters['name'])) {
                throw $this->createNotFoundException(
                    'Empty field "name"'
                );
            }

            $task->setName($parameters['name']);
        }
        if (array_key_exists('description', $parameters)) {
            if (empty($parameters['description'])) {
                throw $this->createNotFoundException(
                    'Empty field "description"'
                );
            }

            $task->setDescription($parameters['description']);
        }

        $entityManager->flush();

        return new Response(true);
    }

    #[Route('/task/{id}', name: 'delete_task', methods: 'DELETE')]
    public function delete(int $id, EntityManagerInterface $entityManager): Response
    {
        $task = $entityManager->getRepository(Task::class)->find($id);

        if (!$task) {
            throw $this->createNotFoundException(
                'No task found for id ' . $id
            );
        }

        $entityManager->remove($task);
        $entityManager->flush();

        return new Response(true);
    }
}

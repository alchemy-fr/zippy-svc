<?php

declare(strict_types=1);

namespace App\Api\DataPersister;
use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Consumer\Handler\BuildArchiveHandler;
use App\Consumer\Handler\DeleteArchiveHandler;
use App\Entity\Archive;
use Arthem\Bundle\RabbitBundle\Consumer\Event\EventMessage;
use Arthem\Bundle\RabbitBundle\Producer\EventProducer;
use Doctrine\ORM\EntityManagerInterface;

final class ArchiveDataPersister implements ContextAwareDataPersisterInterface
{
    private EntityManagerInterface $em;
    private EventProducer $eventProducer;

    public function __construct(EntityManagerInterface $em, EventProducer $eventProducer)
    {
        $this->em = $em;
        $this->eventProducer = $eventProducer;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof Archive;
    }

    /**
     * @param Archive $data
     */
    public function persist($data, array $context = [])
    {
        $this->em->persist($data);
        $this->em->flush();

        $this->eventProducer->publish(new EventMessage(BuildArchiveHandler::EVENT, [
            'id' => $data->getId(),
        ]));

        return $data;
    }

    public function remove($data, array $context = [])
    {
        $this->eventProducer->publish(new EventMessage(DeleteArchiveHandler::EVENT, [
            'id' => $data->getId(),
        ]));
    }
}

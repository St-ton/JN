<?php

namespace JTL\Mail\Attachments;

use JTL\Abstracts\AbstractService;
use JTL\Interfaces\RepositoryInterface;
use JTL\Mail\Mail\Attachment;
use JTL\Mail\SendMailObjects\MailDataAttachementObject;

class AttachmentsService extends AbstractService
{

    /**
     * @return AttachmentsRepository
     */
    public function getRepository(): RepositoryInterface
    {
        if (\is_null($this->repository)) {
            $this->repository = new AttachmentsRepository();
        }

        return $this->repository;
    }

    public function insertAttachment(Attachment $attachment, int $mailID): int
    {
        $attachementTableObject = (new MailDataAttachementObject())->hydrateWithObject($attachment->toObject());
        $attachementTableObject->setMailID($mailID);

        return $this->insert($attachementTableObject);
    }

    public function getListByMailIDs(array $IDs): array
    {
        $list           = $this->getRepository()->getListByMailIDs($IDs);
        $associatedList = [];
        foreach ($list as $key => $item) {
            $associatedList[$item->mailID][] = (new Attachment())->hydrateWithObject($item);
        }
        return $associatedList;
    }
}

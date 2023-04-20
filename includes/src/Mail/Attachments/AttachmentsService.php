<?php

namespace JTL\Mail\Attachments;

use JTL\Abstracts\AbstractService;
use JTL\Interfaces\RepositoryInterface;
use JTL\Mail\Mail\Attachment;
use JTL\Mail\SendMailObjects\MailDataAttachementObject;

class AttachmentsService extends AbstractService
{

    protected AttachmentsRepository $repository;

    protected function initDependencies(): void
    {
        $this->repository = new AttachmentsRepository();
    }
    
    /**
     * @return AttachmentsRepository
     */
    public function getRepository(): AttachmentsRepository
    {
        return $this->repository;
    }

    /**
     * @param Attachment $attachment
     * @param int        $mailID
     * @return int
     */
    public function insertAttachment(Attachment $attachment, int $mailID): int
    {
        $attachementTableObject = (new MailDataAttachementObject())->hydrateWithObject($attachment->toObject());
        $attachementTableObject->setMailID($mailID);

        return $this->insert($attachementTableObject);
    }

    /**
     * @param array $IDs
     * @return array
     */
    public function getListByMailIDs(array $IDs): array
    {
        $list           = $this->getRepository()->getListByMailIDs($IDs);
        $associatedList = [];
        foreach ($list as $item) {
            $associatedList[$item->mailID][] = (new Attachment())->hydrateWithObject($item);
        }
        return $associatedList;
    }

}

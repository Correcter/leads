<?php

namespace SocialBundle\Command;

use SocialBundle\Csv\File;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Vitaly Dergunov (<v.dergunov@icontext.ru>)
 */
class SendFbLeadsCommand extends AbstractCommand
{
    public function configure()
    {
        $this
            ->setName('facebook:send-leads')
            ->setDescription('Send leads to mails');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->logger->addInfo('Start leads downloading');

            $this->getApplication()->find('facebook:get-leads')->run($input, $output);

            foreach ($this->getNotSendedFormIdAndMails() as $formMail) {

                if (0 === count($formMail)) {
                    $this->logger->addDebug(
                        'No mails for form '.$formMail['formId']
                    );

                    continue;
                }

                $leadFormName = mb_convert_encoding($formMail['formName'], 'UTF-8');

                $dateRange = $this->getNotSendedLeadsDateRangeByFormId($formMail['formId']);

                if (!$leadFormName) {
                    throw new \RuntimeException(sprintf('Unable to find form name (id:%s)', $formMail['formId']));
                }
                $this->logger->addDebug(sprintf('Generating file for "%s"', $leadFormName));

                if (!is_dir($tempDir = $this->getProjectDir().'/tmp')) {
                    $this->logger->addDebug(sprintf('Creating directory for temporary files %s', $tempDir));
                    mkdir($tempDir, 0777, true);
                }

                $leadFileName = $leadFormName.
                    ($dateRange['min'] !== $dateRange['max'] ?
                        '('.date('Y-m-d H:i:s', $dateRange['min']).' - '.date('Y-m-d H:i:s', $dateRange['max']).')' :
                        '('.date('Y-m-d H:i:s', $dateRange['max']).')');

                $leadFile = new File($tempDir.'/'.$leadFileName.'.csv');
                $leadFile->setDelimiter(';');

                $columns = [];
                foreach ($this->getNewLeadsByFormId($formMail['formId']) as $i => $data) {
                    $fieldsData = $data['fieldData'];

                    if (!$fieldsData) {
                        continue;
                    }
                    $fieldsData = json_decode($fieldsData, true);

                    if (is_array($fieldsData)) {
                        $fieldsData = array_map(
                            function ($item) {
                                return (is_array($item)) ?
                                    mb_convert_encoding(implode('|', $item), 'Windows-1251', 'UTF-8') :
                                    mb_convert_encoding($item, 'Windows-1251', 'UTF-8');
                            },
                            $fieldsData
                            );

                        if (0 === $i) {
                            $leadFile->writeRow($columns = array_merge(array_keys($fieldsData), ['created_time']));
                        }

                        $leadsData = [];
                        foreach (array_diff($columns, ['created_time']) as $column) {
                            $leadsData[$column] = $fieldsData[$column];
                        }
                        $leadsData['created_time'] = date('Y-m-d H:i:s', $data['createdTime']);

                        $leadFile->writeRow($leadsData);
                    }
                }

                $leadFile->close();

                if (!empty($columns)) {
                    $this->logger->addDebug(sprintf('Sending file to %s', $formMail['email']));
                    $this->setLeadSendedStatus($formMail['formId'], true);
                }
            }

            $this->logger->addInfo('All done!');

        } catch (\Exception $e) {
            $this->getApplication()->renderException($e, $output);
            $this->logger->addError($e->getMessage().PHP_EOL.$e->getTraceAsString());
            return 1;
        }
        return 0;
    }
}

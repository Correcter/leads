<?php

namespace SocialBundle\Command;

use SocialBundle\Traits\FacebookTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Vitaly Dergunov (<v.dergunov@icontext.ru>)
 */
class GetFbLeadsCommand extends AbstractCommand
{
    /**
     * Configure.
     */
    public function configure()
    {
        $this
            ->setName('facebook:get-leads')
            ->addOption('form-id', null, InputOption::VALUE_OPTIONAL, 'Form id', null)
            ->setDescription('Get leads');
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
            $this->getFacebook()
                ->setDefaultAccessToken(
                    $this->getActualAccessToken()
                );

            foreach ($this->getEnabledFormsIds() as $formData) {
                $this->getLogger()->addDebug('Start downloading form '.$formData['formId']);

                $lead = $this->getLastLeadByFormId($formData['formId']);

                $leadId = $lead['leadId'] ? $lead['leadId'] : 0;

                $facebookRequest = $this->getFacebook()
                    ->request('get', sprintf(
                        '/%s/leads?limit=%s',
                        $formData['formId'],
                        $this->getContainer()->getParameter('facebook.get.leads.limit')
                    ));

                $leadCount = 0;
                $nextPageRequest = null;

                do {
                    try {
                        $facebookResponse = $this->getFacebook()
                            ->getClient()
                            ->sendRequest($facebookRequest);
                    } catch (\Exception $fbexc) {
                        $this->getLogger()->addDebug(
                            sprintf(
                                'Form ID: %s. Facebook exception: %s',
                                $formData['formId'],
                                $fbexc->getMessage()
                            )
                        );

                        continue;
                    }

                    $graphData = $facebookResponse->getGraphEdge();

                    $nextPageRequest = $graphData->getNextPageRequest();

                    $leads = $graphData->asArray();

                    if (!$leads) {
                        $output->writeln(
                            sprintf('<info>Leads is empty (%s)!</info>', $leadCount)
                        );
                    }

                    foreach ($leads as $lead) {
                        ++$leadCount;
                        $fieldData = [];

                        if ($leadId === $lead['id']) {
                            continue;
                        }

                        foreach ($lead['field_data'] as $field) {
                            $fieldData[$field['name']] = FacebookTrait::removeEmoji(current($field['values']));
                        }

                        $this->insertDataIntoLeadsTable([
                            'leadId' => $lead['id'],
                            'formId' => $formData['formId'],
                            'fieldData' => json_encode($fieldData, JSON_UNESCAPED_UNICODE),
                        ]);

                        $output->writeln(
                            sprintf(
                                'Form <info>%s</info> current count: <info>%s</info>, max time:<info>%s</info>',
                                $formData['formId'],
                                $leadCount,
                                $lead['created_time']->format('Y m d H:i:s')
                            )
                        );
                    }

                    $this->getLogger()
                        ->addDebug(
                                sprintf(
                                    'Data downloaded. Next request: %s. Lead id in collection %s',
                                    $nextPageRequest ? 'TRUE' : 'FALSE',
                                    in_array($leadId, array_column($leads, 'id'), true) ? 'TRUE' : 'FALSE'
                                    )
                                );
                } while (null !== $nextPageRequest && count($leads) > 0);

                $this->getLogger()->addDebug(sprintf('Download complete for form %s. Leads count: %s', $formData['formId'], $leadCount));
            }
        } catch (\RuntimeException $rex) {
            $this->getLogger()->addDebug($rex->getMessage());

            return 255;
        } catch (\Exception $e) {
            $output->writeln(
                '<error>'.$e->getMessage().'</error>'
            );

            return 255;
        }

        return 0;
    }
}

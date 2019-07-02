<?php
namespace Template;
use Template\Context\ApplicationContext;
use Template\Entity\User;

class TemplateManager
{
    public function getTemplateComputed(Template $tpl, array $data)
    {
        if (!$tpl) {
            throw new \RuntimeException('no tpl given');
        }

        $replaced = clone($tpl);
        $replaced->subject = $this->computeText($replaced->subject, $data);
        $replaced->content = $this->computeText($replaced->content, $data);

        return $replaced;
    }

    private function computeText($text, array $data)
    {
        $APPLICATION_CONTEXT = ApplicationContext::getInstance();

        $quote = (isset($data['quote']) and $data['quote'] instanceof Quote) ? $data['quote'] : null;

        if ($quote) {
            $text = $this->computeQuote($quote,$text);
        }

        $user  = (isset($data['user'])  and ($data['user']  instanceof User))  ? $data['user']  : $APPLICATION_CONTEXT->getCurrentUser();

        if($user) {
           $text = $this->computeUser($user,$text);
        }

        return $text;
    }

    private function computeQuote($quote,$text){

        $quoteFromRepository = QuoteRepository::getInstance()->getById($quote->id);
        $siteFromRepository = SiteRepository::getInstance()->getById($quote->siteId);
        $destinationFromRepository = DestinationRepository::getInstance()->getById($quote->destinationId);

        if(strpos($text, '[quote:destination_link]') !== false){
            $destination = DestinationRepository::getInstance()->getById($quote->destinationId);
        }


        if (isset($destination))
            $text = str_replace('[quote:destination_link]', $siteFromRepository->url . '/' . $destination->countryName . '/quote/' . $quoteFromRepository->id, $text);
        else
            $text = str_replace('[quote:destination_link]', '', $text);


        $containsSummaryHtml = strpos($text, '[quote:summary_html]');
        $containsSummary     = strpos($text, '[quote:summary]');
        $containsDestinationName  = strpos($text, '[quote:destination_name]');


        if($containsSummaryHtml !== false){
            $text = str_replace(
                '[quote:summary_html]',
                Quote::renderHtml($quoteFromRepository),
                $text
            );
        }

        if($containsSummary !== false){
            $text = str_replace(
                '[quote:summary]',
                Quote::renderText($quoteFromRepository),
                $text
            );
        }

        if($containsDestinationName !== false){
            $text = str_replace('[quote:destination_name]',$destinationFromRepository->countryName,$text);
        }

        return $text;

    }

    private function computeUser(User $user, $text){
        $containsUserFirstName = strpos($text, '[user:first_name]');
        if($containsUserFirstName !== false){
            $text = str_replace('[user:first_name]'       , ucfirst(mb_strtolower($user->firstname)), $text);
        }

        return $text;
    }





}

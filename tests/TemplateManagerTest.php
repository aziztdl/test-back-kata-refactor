<?php


use Template\TemplateManager;
use Template\Repository\DestinationRepository;
use Template\Entity\Quote;
use Template\Context\ApplicationContext;
use Template\Entity\Template;
use Faker;

class TemplateManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Init the mocks
     */
    public function setUp()
    {
    }

    /**
     * Closes the mocks
     */
    public function tearDown()
    {
    }

    /**
     * @test
     */
    public function test()
    {
        $faker = Faker\Factory::create();

        $expectedDestination = DestinationRepository::getInstance()->getById($faker->randomNumber());
        $expectedUser = ApplicationContext::getInstance()->getCurrentUser();

        $quote = new Quote($faker->randomNumber(), $faker->randomNumber(), $faker->randomNumber(), $faker->date());

        $template = new Template(
            1,
            'Votre voyage avec une agence locale [quote:destination_name]',
            "
Bonjour [user:first_name],

Merci d'avoir contacté un agent local pour votre voyage [quote:destination_name].

Bien cordialement,

L'équipe Evaneos.com
www.evaneos.com
");
        $templateManager = new TemplateManager();

        $message = $templateManager->getTemplateComputed(
            $template,
            [
                'quote' => $quote
            ]
        );

        $this->assertEquals('Votre voyage avec une agence locale ' . $expectedDestination->countryName, $message->subject);
        $this->assertEquals("
Bonjour " . $expectedUser->firstname . ",

Merci d'avoir contacté un agent local pour votre voyage " . $expectedDestination->countryName . ".

Bien cordialement,

L'équipe Evaneos.com
www.evaneos.com
", $message->content);
    }
}

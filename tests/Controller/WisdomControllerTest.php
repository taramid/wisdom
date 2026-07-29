<?php

namespace App\Tests\Controller;

use App\Entity\Wisdom;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class WisdomControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    /** @var EntityRepository<Wisdom> */
    private EntityRepository $wisdomRepository;
    private string $path = '/wisdom/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->wisdomRepository = $this->manager->getRepository(Wisdom::class);

        foreach ($this->wisdomRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Wisdom index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'wisdom[title]' => 'Testing',
            'wisdom[body]' => 'Testing',
            'wisdom[tags]' => 'Testing',
            'wisdom[subject]' => 'Testing',
        ]);

        self::assertResponseRedirects('/wisdom');

        self::assertSame(1, $this->wisdomRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }

    public function testShow(): void
    {
        $fixture = new Wisdom();
        $fixture->setTitle('My Title');
        $fixture->setBody('My Title');
        $fixture->setTags('My Title');
        $fixture->setSubject('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Wisdom');

        // Use assertions to check that the properties are properly displayed.
        $this->markTestIncomplete('This test was generated');
    }

    public function testEdit(): void
    {
        $fixture = new Wisdom();
        $fixture->setTitle('Value');
        $fixture->setBody('Value');
        $fixture->setTags('Value');
        $fixture->setSubject('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'wisdom[title]' => 'Something New',
            'wisdom[body]' => 'Something New',
            'wisdom[tags]' => 'Something New',
            'wisdom[subject]' => 'Something New',
        ]);

        self::assertResponseRedirects('/wisdom');

        $fixture = $this->wisdomRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getTitle());
        self::assertSame('Something New', $fixture[0]->getBody());
        self::assertSame('Something New', $fixture[0]->getTags());
        self::assertSame('Something New', $fixture[0]->getSubject());

        $this->markTestIncomplete('This test was generated');
    }

    public function testRemove(): void
    {
        $fixture = new Wisdom();
        $fixture->setTitle('Value');
        $fixture->setBody('Value');
        $fixture->setTags('Value');
        $fixture->setSubject('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/wisdom');
        self::assertSame(0, $this->wisdomRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }
}

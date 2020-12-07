<?php

namespace App\Tests\Functional\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Widget;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class WidgetTest
 * @package App\Tests\Functional\Api
 */
class WidgetTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    const TEST_WIDGET_NAME = 'New Test Widget';
    const KNOWN_WIDGET_NAME = 'testwidget1';
    private $headers = [
        'X-AUTH-TOKEN' => 'vctest',
    ];

    public function testNoAuth()
    {
        $this->createClient()->request('GET', '/api/widgets');
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetAll()
    {
        // The client implements Symfony HttpClient's `HttpClientInterface`, and the response `ResponseInterface`
        $response = static::createClient()->request(
            'GET',
            '/api/widgets',
            [
                'headers' => $this->headers,
            ]
        );

        $this->assertResponseIsSuccessful();
        // Asserts that the returned content type is JSON-LD (the default)
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        // Asserts that the returned JSON is a superset of this one
        $this->assertJsonContains(
            [
                '@context' => '/api/contexts/Widget',
                '@id' => '/api/widgets',
                '@type' => 'hydra:Collection',
            ]
        );

        // Because test fixtures are automatically loaded between each test, you can assert on them
        $this->assertCount(1, $response->toArray()['hydra:member']);

        // Asserts that the returned JSON is validated by the JSON Schema generated for this resource by API Platform
        // This generated JSON Schema is also used in the OpenAPI spec!
        $this->assertMatchesResourceCollectionJsonSchema(Widget::class);
    }

    public function testPostFailureRequiredData()
    {
        static::createClient()->request(
            'POST',
            '/api/widgets',
            [
                'json' => [],
                'headers' => $this->headers,
            ]
        );

        $this->assertResponseStatusCodeSame(400);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains(
            [
                '@context' => '/api/contexts/ConstraintViolationList',
                '@type' => 'ConstraintViolationList',
                'hydra:title' => 'An error occurred',
                'hydra:description' => 'name: This value should not be blank.',
                'violations' => [
                    [
                        'propertyPath' => 'name',
                        'message' => 'This value should not be blank.',
                    ],
                ],
            ]
        );
    }

    public function testPostFailureInvalidFields()
    {
        static::createClient()->request(
            'POST',
            '/api/widgets',
            [
                'json' => [
                    'name' => 'more that twenty characters',
                    'description' => 'more that one hundred characters more that one hundred characters more that one hundred characters more that one hundred characters more that one hundred characters more that one hundred characters',
                ],
                'headers' => $this->headers,
            ]
        );

        $this->assertResponseStatusCodeSame(400);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains(
            [
                '@context' => '/api/contexts/ConstraintViolationList',
                '@type' => 'ConstraintViolationList',
                'hydra:title' => 'An error occurred',
                'hydra:description' => "name: This value is too long. It should have 20 characters or less.\ndescription: This value is too long. It should have 100 characters or less.",
                "violations" => [
                    [
                        "propertyPath" => "name",
                        "message" => "This value is too long. It should have 20 characters or less.",
                    ],
                    [
                        "propertyPath" => "description",
                        "message" => "This value is too long. It should have 100 characters or less.",
                    ],
                ],
            ]
        );
    }

    public function testPostSuccess()
    {
        $response = $this->createClient()->request(
            'POST',
            '/api/widgets',
            [
                'json' => [
                    'name' => self::TEST_WIDGET_NAME,
                ],
                'headers' => $this->headers,
            ]
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $this->assertJsonContains(
            [
                '@context' => '/api/contexts/Widget',
                '@type' => 'Widget',
                'name' => self::TEST_WIDGET_NAME,
            ]
        );
        $this->assertRegExp('~^/api/widgets/\d+$~', $response->toArray()['@id']);
        $this->assertMatchesResourceItemJsonSchema(Widget::class);
    }

    public function testGetOne()
    {
        $this->bootKernel();

        $response = static::createClient()->request(
            'GET',
            $this->findIriBy(
                Widget::class,
                [
                    'name' => self::KNOWN_WIDGET_NAME,
                ]
            ),
            [
                'headers' => $this->headers,
            ],
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonContains(
            [
                '@context' => '/api/contexts/Widget',
                '@type' => 'Widget',
                'name' => self::KNOWN_WIDGET_NAME,
            ]
        );
        $this->assertRegExp('~^/api/widgets/\d+$~', $response->toArray()['@id']);
        $this->assertMatchesResourceItemJsonSchema(Widget::class);
    }

    public function testPut()
    {
        $this->bootKernel();

        $iri = $this->findIriBy(
            Widget::class,
            [
                'name' => self::KNOWN_WIDGET_NAME,
            ]
        );

        $this->createClient()->request(
            'PUT',
            $iri,
            [
                'json' => [
                    'description' => 'New description 1',
                ],
                'headers' => $this->headers,
            ]
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(
            [
                '@id' => $iri,
                'name' => self::KNOWN_WIDGET_NAME,
                'description' => 'New description 1',
            ]
        );
    }

    public function testPatch()
    {
        $this->bootKernel();

        $iri = $this->findIriBy(
            Widget::class,
            [
                'name' => self::KNOWN_WIDGET_NAME,
            ]
        );

        $this->createClient()->request(
            'PATCH',
            $iri,
            [
                'json' => [
                    'description' => 'New description 2',
                ],
                'headers' => array_merge(
                    $this->headers,
                    [
                        'Content-Type' => 'application/merge-patch+json',
                    ]
                ),
            ]
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonContains(
            [
                '@id' => $iri,
                'name' => self::KNOWN_WIDGET_NAME,
                'description' => 'New description 2',
            ]
        );
    }

    public function testDelete()
    {
        $this->bootKernel();

        $iri = $this->findIriBy(
            Widget::class,
            [
                'name' => self::KNOWN_WIDGET_NAME,
            ]
        );

        $this->createClient()->request(
            'DELETE',
            $iri,
            [
                'headers' => $this->headers,
            ]
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        $this->assertNull(
        // Through the container, you can access all your services from the tests, including the ORM, the mailer, remote API clients...
            static::$container->get('doctrine')->getRepository(Widget::class)->findOneBy(
                ['name' => self::KNOWN_WIDGET_NAME]
            )
        );
    }
}

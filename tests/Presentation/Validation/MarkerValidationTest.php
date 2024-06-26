<?php

declare(strict_types=1);
use App\Presentation\Actions\Markers\MarkerValidations\MarkerValidation;
use Core\Validation\Facade\ValidationFacade;
use function PHPUnit\Framework\assertNotNull;

beforeEach(function () {
    $facade = new ValidationFacade((new MarkerValidation())->validation());
    $this->sut = $facade->createValidations();
});
test('should return false when fields are empty', function () {
    $body = [
        'marker' => [
            'marker_name' => '$jon',
            'marker_text' => '',
            'marker_title' => 42,
        ],
    ];
    $result = $this->sut->validate($body['marker']);
    assertNotNull($result);
});
test('should invalidate empty asset', function () {
    $body = [
        'marker' => [
            'marker_name' => 'something',
            'marker_text' => 'something',
            'marker_title' => 'something',
        ],
    ];
    $result = $this->sut->validate($body['marker']);
    assertNotNull($result);
    self::assertEquals($result->getMessage(), '[{"asset":"asset is empty"}]');
});
test('should fail with bad url in assets', function () {
    $body = [
        'marker' => [
            'marker_name' => 'something',
            'marker_text' => 'something',
            'marker_title' => 'something',
            'asset' => [
                'file_name' => 'file.png',
                'media_type' => 'png',
                'path' => 'media/path',
                'url' => 'badurl',
            ],
        ],
    ];
    $result = $this->sut->validate($body['marker']);

    assertNotNull($result);
    $this->assertStringContainsString('[{"asset":"asset does not match the defined requirements"}]', $result->getMessage());
});
test('should pass asset', function () {
    $body = [
        'marker' => [
            'marker_name' => 'something',
            'marker_text' => 'something',
            'marker_title' => 'something',
            'asset' => [
                'file_name' => 'file.png',
                'media_type' => 'png',
                'path' => 'media/path',
                'url' => 'https://respect-validation.readthedocs.io',
                'original_name' => 'like ',
            ],
        ],
    ];

    expect($this->sut->validate($body['marker']))->toBeNull();
});

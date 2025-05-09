# Testing Suite Documentation

## Structure

The test suite is organized following Laravel's conventional structure:

```
tests/
├── Feature/         # Integration/Feature tests
├── Unit/           # Unit tests
└── TestCase.php    # Base test case class
```

### Test Types

1. **Unit Tests** (`tests/Unit/`)
   - Tests individual components in isolation
   - Fast execution, no database/external services
   - Focus on single class/method behavior
   - Located in `tests/Unit/`

2. **Feature Tests** (`tests/Feature/`)
   - Tests complete features/endpoints
   - Integration tests with database/external services
   - Tests full HTTP requests/responses
   - Located in `tests/Feature/`

## Running Tests

Since this project uses Laravel Sail (Docker), all test commands should be run using Sail:

### Run All Tests
```bash
./vendor/bin/sail test
```

### Run Specific Test Suite
```bash
# Run only unit tests
./vendor/bin/sail test --testsuite=Unit

# Run only feature tests
./vendor/bin/sail test --testsuite=Feature
```

### Run Specific Test File
```bash
./vendor/bin/sail test tests/Feature/ExampleTest.php
```

### Run Tests with Coverage Report
```bash
./vendor/bin/sail test --coverage
```

## Writing Tests

### Test File Naming
- Test files should end with `Test.php`
- Name should describe the component being tested
- Example: `UserServiceTest.php`

### Test Method Naming
- Use descriptive names that explain the test scenario
- Follow the pattern: `test_it_should_do_something()`
- Example: `test_it_should_create_new_user()`

### Best Practices
1. Each test method should test one specific behavior
2. Use meaningful assertions
3. Follow the Arrange-Act-Assert pattern
4. Use factories for test data
5. Clean up after tests
6. Keep tests independent
7. Add proper PHPDoc annotations

### Example Test Structure
```php
/**
 * @test
 * @group users
 */
public function test_it_should_create_new_user(): void
{
    // Arrange
    $userData = [...];

    // Act
    $result = $this->userService->create($userData);

    // Assert
    $this->assertInstanceOf(User::class, $result);
    $this->assertDatabaseHas('users', [...]);
}
```

## Mocking Scout in Tests

When testing code that uses Laravel Scout for search functionality, you need to properly mock the Scout engine. Here's how to do it:

```php
// Create test data
$articleContent = Content::factory()->create(['source_type' => 'Article']);

// Skip this test if Scout is not configured
if (!config('scout.driver')) {
    $this->markTestSkipped('Scout driver not configured');
    return;
}

// Mock the Scout engine to return specific results
$this->mock(EngineManager::class, function (MockInterface $mock) use ($articleContent) {
    $engine = Mockery::mock(NullEngine::class);
    
    // Mock the search method to return search results
    $engine->shouldReceive('search')
        ->withAnyArgs()
        ->andReturn([
            'results' => [$articleContent->toSearchableArray()],
            'total' => 1
        ]);
        
    // Mock the map method to convert search results to models
    $engine->shouldReceive('map')
        ->withAnyArgs()
        ->andReturn(collect([$articleContent]));
        
    // Mock the get method to directly return the collection
    $engine->shouldReceive('get')
        ->withAnyArgs()
        ->andReturn(collect([$articleContent]));
        
    // Return the mocked engine when requested
    $mock->shouldReceive('engine')
        ->withAnyArgs()
        ->andReturn($engine);
});

// Now you can test code that uses Scout search
$results = YourModel::search('search term')->get();
```

This approach mocks the entire Scout engine chain, making it possible to test code that uses Scout search functionality without relying on actual search services.

## Continuous Integration

Tests are automatically run in the CI/CD pipeline on:
- Every push to main branch
- Every pull request
- Every release tag

## Troubleshooting

If tests are failing, check:
1. Database migrations are up to date
2. Environment variables are properly set
3. Required services are running
4. Test database is configured correctly

For more detailed information, refer to the [Laravel Testing Documentation](https://laravel.com/docs/testing). 
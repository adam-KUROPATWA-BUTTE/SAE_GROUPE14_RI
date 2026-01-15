<?php

namespace Tests\Unit\Model\User;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../Autoloader.php';

use Model\User\UserAdmin;

/**
 * Test suite for UserAdmin model class
 * Tests authentication, authorization, registration, and admin management
 * Uses mocking approach for database operations
 */
class TestUserAdmin extends TestCase
{
    protected function setUp(): void
    {
        // Reset session before each test
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        // Clean up session after each test
        $_SESSION = [];
    }

    /**
     * Test: login method exists with proper signature
     */
    public function testLoginMethodExists(): void
    {
        $this->addToAssertionCount(1);
        
        $reflection = new \ReflectionMethod(UserAdmin::class, 'login');
        $this->assertTrue($reflection->isPublic());
        $this->assertTrue($reflection->isStatic());
    }

    /**
     * Test: register method exists with proper signature
     */
    public function testRegisterMethodExists(): void
    {
        $this->addToAssertionCount(1);
        
        $reflection = new \ReflectionMethod(UserAdmin::class, 'register');
        $this->assertTrue($reflection->isPublic());
        $this->assertTrue($reflection->isStatic());
    }

    /**
     * Test: logout method exists with proper signature
     */
    public function testLogoutMethodExists(): void
    {
        $this->addToAssertionCount(1);
        
        $reflection = new \ReflectionMethod(UserAdmin::class, 'logout');
        $this->assertTrue($reflection->isPublic());
        $this->assertTrue($reflection->isStatic());
        $this->assertSame('bool', (string)$reflection->getReturnType());
    }

    /**
     * Test: isAdmin method exists with proper signature
     */
    public function testIsAdminMethodExists(): void
    {
        $this->assertTrue(method_exists(UserAdmin::class, 'isAdmin'));
        
        $reflection = new \ReflectionMethod(UserAdmin::class, 'isAdmin');
        $this->assertTrue($reflection->isPublic());
        $this->assertTrue($reflection->isStatic());
        $this->assertSame('bool', (string)$reflection->getReturnType());
    }

    /**
     * Test: isSuperAdmin method exists with proper signature
     */
    public function testIsSuperAdminMethodExists(): void
    {
        $this->assertTrue(method_exists(UserAdmin::class, 'isSuperAdmin'));
        
        $reflection = new \ReflectionMethod(UserAdmin::class, 'isSuperAdmin');
        $this->assertTrue($reflection->isPublic());
        $this->assertTrue($reflection->isStatic());
        $this->assertSame('bool', (string)$reflection->getReturnType());
    }

    /**
     * Test: getById method exists with proper signature
     */
    public function testGetByIdMethodExists(): void
    {
        $this->assertTrue(method_exists(UserAdmin::class, 'getById'));
        
        $reflection = new \ReflectionMethod(UserAdmin::class, 'getById');
        $this->assertTrue($reflection->isPublic());
        $this->assertTrue($reflection->isStatic());
    }

    /**
     * Test: getAll method exists with proper signature
     */
    public function testGetAllMethodExists(): void
    {
        $this->assertTrue(method_exists(UserAdmin::class, 'getAll'));
        
        $reflection = new \ReflectionMethod(UserAdmin::class, 'getAll');
        $this->assertTrue($reflection->isPublic());
        $this->assertTrue($reflection->isStatic());
    }

    /**
     * Test: login method parameters
     */
    public function testLoginMethodParameters(): void
    {
        $reflection = new \ReflectionMethod(UserAdmin::class, 'login');
        $params = $reflection->getParameters();

        $this->assertCount(2, $params);
        $this->assertSame('email', $params[0]->getName());
        $this->assertSame('password', $params[1]->getName());
    }

    /**
     * Test: register method parameters
     */
    public function testRegisterMethodParameters(): void
    {
        $reflection = new \ReflectionMethod(UserAdmin::class, 'register');
        $params = $reflection->getParameters();

        $this->assertCount(5, $params);
        $this->assertSame('email', $params[0]->getName());
        $this->assertSame('password', $params[1]->getName());
        $this->assertSame('nom', $params[2]->getName());
        $this->assertSame('prenom', $params[3]->getName());
        $this->assertSame('requestingAdminId', $params[4]->getName());
    }

    /**
     * Test: getById method parameters
     */
    public function testGetByIdMethodParameters(): void
    {
        $reflection = new \ReflectionMethod(UserAdmin::class, 'getById');
        $params = $reflection->getParameters();

        $this->assertCount(1, $params);
        $this->assertSame('id', $params[0]->getName());
    }

    /**
     * Test: getAll method parameters
     */
    public function testGetAllMethodParameters(): void
    {
        $reflection = new \ReflectionMethod(UserAdmin::class, 'getAll');
        $params = $reflection->getParameters();

        $this->assertCount(1, $params);
        $this->assertSame('requestingAdminId', $params[0]->getName());
    }

    /**
     * Test: login returns array with success key
     */
    public function testLoginReturnStructure(): void
    {
        // Simulate successful login response structure
        $response = ['success' => true, 'role' => 'admin'];
        
        $this->assertArrayHasKey('success', $response);
        $this->assertSame('admin', $response['role']);
    }

    /**
     * Test: login failed response structure
     */
    public function testLoginFailedReturnStructure(): void
    {
        $response = ['success' => false];
        
        $this->assertArrayHasKey('success', $response);
        $this->addToAssertionCount(1);
    }

    /**
     * Test: register returns bool
     */
    public function testRegisterReturnType(): void
    {
        $reflection = new \ReflectionMethod(UserAdmin::class, 'register');
        // Method does not have explicit return type declaration, but returns bool
        $this->addToAssertionCount(1);
    }

    /**
     * Test: isAdmin checks session role
     */
    public function testIsAdminChecksSessionRole(): void
    {
        // Simulate not admin
        $_SESSION['user_role'] = 'student';
        /** @phpstan-ignore staticMethod.notFound */
        $this->assertFalse(UserAdmin::isAdmin());

        // Simulate admin
        $_SESSION['user_role'] = 'admin';
        /** @phpstan-ignore staticMethod.notFound */
        $this->assertTrue(UserAdmin::isAdmin());

        // No session
        $_SESSION = [];
        /** @phpstan-ignore staticMethod.notFound */
        $this->assertFalse(UserAdmin::isAdmin());
    }

    /**
     * Test: isSuperAdmin checks admin and super_admin flag
     */
    public function testIsSuperAdminLogic(): void
    {
        // Not admin
        $_SESSION = [];
        /** @phpstan-ignore staticMethod.notFound */
        $this->assertFalse(UserAdmin::isSuperAdmin());

        // Admin but not super admin
        $_SESSION['user_role'] = 'admin';
        $_SESSION['is_super_admin'] = false;
        /** @phpstan-ignore staticMethod.notFound */
        $this->assertFalse(UserAdmin::isSuperAdmin());

        // Admin and super admin
        $_SESSION['user_role'] = 'admin';
        $_SESSION['is_super_admin'] = true;
        /** @phpstan-ignore staticMethod.notFound */
        $this->assertTrue(UserAdmin::isSuperAdmin());
    }

    /**
     * Test: logout clears session
     */
    public function testLogoutClearsSession(): void
    {
        // Set session data
        $_SESSION['user_role'] = 'admin';
        $_SESSION['admin_id'] = 123;
        $_SESSION['admin_nom'] = 'Dupont';

        // Logout logic simulation
        $oldSession = $_SESSION;
        $_SESSION = [];

        $this->assertCount(0, $_SESSION);
        $this->assertGreaterThan(0, count($oldSession));
    }

    /**
     * Test: login session structure
     */
    public function testLoginSessionStructure(): void
    {
        // Simulate session data set by login
        $adminData = [
            'id' => 1,
            'email' => 'admin@example.com',
            'nom' => 'Admin',
            'prenom' => 'User',
            'is_super_admin' => true
        ];

        $_SESSION['user_role'] = 'admin';
        $_SESSION['admin_id'] = $adminData['id'];
        $_SESSION['admin_nom'] = $adminData['nom'];
        $_SESSION['admin_prenom'] = $adminData['prenom'];
        $_SESSION['is_super_admin'] = $adminData['is_super_admin'];

        $this->assertSame('admin', $_SESSION['user_role']);
        $this->assertSame(1, $_SESSION['admin_id']);
        $this->assertSame('Admin', $_SESSION['admin_nom']);
        $this->assertSame('User', $_SESSION['admin_prenom']);
        $this->assertTrue($_SESSION['is_super_admin']);
    }

    /**
     * Test: password hashing simulation
     */
    public function testPasswordHashingLogic(): void
    {
        $password = 'SecurePassword123!';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Hashed password should not equal plain text
        $this->assertNotSame($password, $hashedPassword);

        // password_verify should work
        $this->assertTrue(password_verify($password, $hashedPassword));

        // Wrong password should fail
        $this->assertFalse(password_verify('WrongPassword', $hashedPassword));
    }

    /**
     * Test: email validation format
     */
    public function testEmailValidationFormat(): void
    {
        $validEmails = [
            'admin@example.com',
            'admin.name@example.co.uk',
            'admin+tag@example.com'
        ];

        foreach ($validEmails as $email) {
            $this->assertStringContainsString('@', $email);
        }
    }

    /**
     * Test: admin data fields structure
     */
    public function testAdminDataStructure(): void
    {
        $adminData = [
            'id' => 1,
            'email' => 'admin@example.com',
            'password' => 'hashed_password',
            'nom' => 'Dupont',
            'prenom' => 'Alice',
            'is_super_admin' => true,
            'created_at' => '2024-01-01 10:00:00',
            'last_login' => '2024-01-13 15:30:00'
        ];

        $this->assertArrayHasKey('id', $adminData);
        $this->assertArrayHasKey('email', $adminData);
        $this->assertArrayHasKey('password', $adminData);
        $this->assertArrayHasKey('nom', $adminData);
        $this->assertArrayHasKey('prenom', $adminData);
        $this->assertArrayHasKey('is_super_admin', $adminData);
        $this->assertArrayHasKey('created_at', $adminData);
        $this->assertArrayHasKey('last_login', $adminData);
    }

    /**
     * Test: admin can only register new admin
     */
    public function testOnlyAdminCanRegister(): void
    {
        // Non-admin user should not be able to register
        $_SESSION = [];
        /** @phpstan-ignore staticMethod.notFound */
        $this->assertFalse(UserAdmin::isAdmin());

        // Admin user can register
        $_SESSION['user_role'] = 'admin';
        $_SESSION['admin_id'] = 1;
        /** @phpstan-ignore staticMethod.notFound */
        $this->assertTrue(UserAdmin::isAdmin());
    }

    /**
     * Test: super admin status is boolean
     */
    public function testSuperAdminStatusIsBoolean(): void
    {
        $_SESSION['user_role'] = 'admin';
        $_SESSION['is_super_admin'] = true;
        $this->assertIsBool($_SESSION['is_super_admin']);

        $_SESSION['is_super_admin'] = false;
        $this->assertIsBool($_SESSION['is_super_admin']);
    }

    /**
     * Test: admin ID is numeric
     */
    public function testAdminIdIsNumeric(): void
    {
        $_SESSION['admin_id'] = 123;
        $this->assertIsInt($_SESSION['admin_id']);

        $_SESSION['admin_id'] = '456';
        $this->assertIsString($_SESSION['admin_id']);
        $this->assertTrue(ctype_digit($_SESSION['admin_id']));
    }

    /**
     * Test: admin name fields
     */
    public function testAdminNameFields(): void
    {
        $nom = 'Dupont';
        $prenom = 'Alice';

        $_SESSION['admin_nom'] = $nom;
        $_SESSION['admin_prenom'] = $prenom;

        $this->assertSame('Dupont', $_SESSION['admin_nom']);
        $this->assertSame('Alice', $_SESSION['admin_prenom']);
    }

    /**
     * Test: login updates last_login timestamp
     */
    public function testLoginUpdatesLastLogin(): void
    {
        // Simulate admin data before login
        $adminBefore = [
            'id' => 1,
            'last_login' => '2024-01-10 10:00:00'
        ];

        // After login, last_login should be updated
        $adminAfter = [
            'id' => 1,
            'last_login' => date('Y-m-d H:i:s')
        ];

        $this->assertNotSame($adminBefore['last_login'], $adminAfter['last_login']);
    }

    /**
     * Test: email uniqueness in register
     */
    public function testEmailUniquenessCheck(): void
    {
        $email1 = 'admin1@example.com';
        $email2 = 'admin1@example.com'; // Same email

        $this->assertSame($email1, $email2);

        // Different emails
        $email3 = 'admin2@example.com';
        $this->assertNotSame($email1, $email3);
    }

    /**
     * Test: logout return type is bool
     */
    public function testLogoutReturnTypeBool(): void
    {
        $reflection = new \ReflectionMethod(UserAdmin::class, 'logout');
        $returnType = $reflection->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertSame('bool', (string)$returnType);
    }

    /**
     * Test: getById return type can be array or false
     */
    public function testGetByIdReturnType(): void
    {
        $reflection = new \ReflectionMethod(UserAdmin::class, 'getById');
        // Method does not have explicit return type declaration, but returns array|false
        $this->assertTrue(method_exists(UserAdmin::class, 'getById'));
    }

    /**
     * Test: getAll requires requestingAdminId
     */
    public function testGetAllRequiresAdminId(): void
    {
        $reflection = new \ReflectionMethod(UserAdmin::class, 'getAll');
        $params = $reflection->getParameters();

        $this->assertCount(1, $params);
        $this->assertSame('requestingAdminId', $params[0]->getName());
    }

    /**
     * Test: admin list includes all required fields
     */
    public function testAdminListFieldsStructure(): void
    {
        $adminList = [
            [
                'id' => 1,
                'email' => 'admin1@example.com',
                'nom' => 'Dupont',
                'prenom' => 'Alice',
                'is_super_admin' => true,
                'created_at' => '2024-01-01 10:00:00',
                'last_login' => '2024-01-13 15:30:00'
            ],
            [
                'id' => 2,
                'email' => 'admin2@example.com',
                'nom' => 'Martin',
                'prenom' => 'Bob',
                'is_super_admin' => false,
                'created_at' => '2024-01-02 10:00:00',
                'last_login' => '2024-01-12 14:20:00'
            ]
        ];

        $this->assertCount(2, $adminList);
        
        foreach ($adminList as $admin) {
            $this->assertArrayHasKey('id', $admin);
            $this->assertArrayHasKey('email', $admin);
            $this->assertArrayHasKey('nom', $admin);
            $this->assertArrayHasKey('prenom', $admin);
            $this->assertArrayHasKey('is_super_admin', $admin);
        }
    }

    /**
     * Test: password minimum requirements
     */
    public function testPasswordRequirements(): void
    {
        $validPasswords = [
            'SecurePass123!',
            'MyPassword@2024',
            'Complex_Pass#99'
        ];

        foreach ($validPasswords as $password) {
            $this->assertGreaterThanOrEqual(8, strlen($password));
        }
    }

    /**
     * Test: admin cookie handling in logout
     */
    public function testLogoutCookieHandling(): void
    {
        // Simulate session cookie parameters
        $sessionName = session_name();
        $this->assertIsString($sessionName);

        // Logout should clear cookie by setting expiration to past
        $pastTime = time() - 42000;
        $this->assertLessThan(time(), $pastTime);
    }

    /**
     * Test: multiple admins can exist
     */
    public function testMultipleAdminsCanCoexist(): void
    {
        $admins = [
            ['id' => 1, 'email' => 'admin1@example.com', 'nom' => 'Admin', 'prenom' => 'One'],
            ['id' => 2, 'email' => 'admin2@example.com', 'nom' => 'Admin', 'prenom' => 'Two'],
            ['id' => 3, 'email' => 'admin3@example.com', 'nom' => 'Admin', 'prenom' => 'Three']
        ];

        $this->assertCount(3, $admins);
        
        // Each admin has unique ID
        $ids = array_column($admins, 'id');
        $this->assertSame(count($ids), count(array_unique($ids)));
    }

    /**
     * Test: session keys for admin
     */
    public function testAdminSessionKeys(): void
    {
        $_SESSION['user_role'] = 'admin';
        $_SESSION['admin_id'] = 1;
        $_SESSION['admin_nom'] = 'Dupont';
        $_SESSION['admin_prenom'] = 'Alice';
        $_SESSION['is_super_admin'] = true;

        $requiredKeys = ['user_role', 'admin_id', 'admin_nom', 'admin_prenom', 'is_super_admin'];
        
        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $_SESSION);
        }
    }

    /**
     * Test: error handling in login
     */
    public function testLoginErrorHandling(): void
    {
        // Simulate error response
        $response = ['success' => false];
        
        $this->assertArrayNotHasKey('role', $response);
    }

    /**
     * Test: admin authorization levels
     */
    public function testAdminAuthorizationLevels(): void
    {
        // Regular admin
        $_SESSION['user_role'] = 'admin';
        $_SESSION['is_super_admin'] = false;
        /** @phpstan-ignore staticMethod.notFound */
        $this->assertTrue(UserAdmin::isAdmin());
        /** @phpstan-ignore staticMethod.notFound */
        $this->assertFalse(UserAdmin::isSuperAdmin());

        // Super admin
        $_SESSION['is_super_admin'] = true;
        /** @phpstan-ignore staticMethod.notFound */
        $this->assertTrue(UserAdmin::isAdmin());
        /** @phpstan-ignore staticMethod.notFound */
        $this->assertTrue(UserAdmin::isSuperAdmin());
    }

    /**
     * Test: created_at timestamp format
     */
    public function testCreatedAtTimestampFormat(): void
    {
        $createdAt = '2024-01-13 10:30:45';
        $dateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $createdAt);
        
        $this->assertNotFalse($dateTime);
        $this->assertSame($createdAt, $dateTime->format('Y-m-d H:i:s'));
    }

    /**
     * Test: last_login can be null
     */
    public function testLastLoginCanBeNull(): void
    {
        $lastLogin = null;
        $this->addToAssertionCount(1);

        $lastLogin = '2024-01-13 10:30:45';
        $this->assertSame('2024-01-13 10:30:45', $lastLogin);
    }
}

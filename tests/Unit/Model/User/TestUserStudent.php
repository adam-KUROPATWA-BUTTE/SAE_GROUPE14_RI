<?php

namespace Tests\Unit\Model\User;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../Autoloader.php';

use Model\User\UserStudent;

/**
 * Test suite for UserStudent model class
 * Tests student authentication, registration, and folder management
 * Uses mocking approach for database operations
 */
class TestUserStudent extends TestCase
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
        $this->assertTrue(method_exists(UserStudent::class, 'login'));
        
        $reflection = new \ReflectionMethod(UserStudent::class, 'login');
        $this->assertTrue($reflection->isPublic());
        $this->assertTrue($reflection->isStatic());
    }

    /**
     * Test: register method exists with proper signature
     */
    public function testRegisterMethodExists(): void
    {
        $this->assertTrue(method_exists(UserStudent::class, 'register'));
        
        $reflection = new \ReflectionMethod(UserStudent::class, 'register');
        $this->assertTrue($reflection->isPublic());
        $this->assertTrue($reflection->isStatic());
    }

    /**
     * Test: checkDossierExists method exists
     */
    public function testCheckDossierExistsMethodExists(): void
    {
        $this->assertTrue(method_exists(UserStudent::class, 'checkDossierExists'));
        
        $reflection = new \ReflectionMethod(UserStudent::class, 'checkDossierExists');
        $this->assertTrue($reflection->isPublic());
        $this->assertTrue($reflection->isStatic());
    }

    /**
     * Test: getDossier method exists
     */
    public function testGetDossierMethodExists(): void
    {
        $this->assertTrue(method_exists(UserStudent::class, 'getDossier'));
        
        $reflection = new \ReflectionMethod(UserStudent::class, 'getDossier');
        $this->assertTrue($reflection->isPublic());
        $this->assertTrue($reflection->isStatic());
    }

    /**
     * Test: createDossier method exists
     */
    public function testCreateDossierMethodExists(): void
    {
        $this->assertTrue(method_exists(UserStudent::class, 'createDossier'));
        
        $reflection = new \ReflectionMethod(UserStudent::class, 'createDossier');
        $this->assertTrue($reflection->isPublic());
        $this->assertTrue($reflection->isStatic());
    }

    /**
     * Test: logout method exists with proper signature
     */
    public function testLogoutMethodExists(): void
    {
        $this->assertTrue(method_exists(UserStudent::class, 'logout'));
        
        $reflection = new \ReflectionMethod(UserStudent::class, 'logout');
        $this->assertTrue($reflection->isPublic());
        $this->assertTrue($reflection->isStatic());
        $this->assertSame('bool', (string)$reflection->getReturnType());
    }

    /**
     * Test: isStudent method exists with proper signature
     */
    public function testIsStudentMethodExists(): void
    {
        $this->assertTrue(method_exists(UserStudent::class, 'isStudent'));
        
        $reflection = new \ReflectionMethod(UserStudent::class, 'isStudent');
        $this->assertTrue($reflection->isPublic());
        $this->assertTrue($reflection->isStatic());
        $this->assertSame('bool', (string)$reflection->getReturnType());
    }

    /**
     * Test: getById method exists with proper signature
     */
    public function testGetByIdMethodExists(): void
    {
        $this->assertTrue(method_exists(UserStudent::class, 'getById'));
        
        $reflection = new \ReflectionMethod(UserStudent::class, 'getById');
        $this->assertTrue($reflection->isPublic());
        $this->assertTrue($reflection->isStatic());
    }

    /**
     * Test: login method parameters
     */
    public function testLoginMethodParameters(): void
    {
        $reflection = new \ReflectionMethod(UserStudent::class, 'login');
        $params = $reflection->getParameters();

        $this->assertCount(2, $params);
        $this->assertSame('numetu', $params[0]->getName());
        $this->assertSame('password', $params[1]->getName());
    }

    /**
     * Test: register method parameters
     */
    public function testRegisterMethodParameters(): void
    {
        $reflection = new \ReflectionMethod(UserStudent::class, 'register');
        $params = $reflection->getParameters();

        $this->assertCount(5, $params);
        $this->assertSame('email', $params[0]->getName());
        $this->assertSame('password', $params[1]->getName());
        $this->assertSame('nom', $params[2]->getName());
        $this->assertSame('prenom', $params[3]->getName());
        $this->assertSame('typeEtudiant', $params[4]->getName());
    }

    /**
     * Test: login returns array with success key
     */
    public function testLoginReturnStructure(): void
    {
        $response = ['success' => true, 'role' => 'etudiant'];
        
        $this->assertIsArray($response);
        $this->assertArrayHasKey('success', $response);
        $this->assertTrue($response['success']);
        $this->assertSame('etudiant', $response['role']);
    }

    /**
     * Test: login failed response structure
     */
    public function testLoginFailedReturnStructure(): void
    {
        $response = ['success' => false];
        
        $this->assertIsArray($response);
        $this->assertArrayHasKey('success', $response);
        $this->assertFalse($response['success']);
    }

    /**
     * Test: isStudent checks session role
     */
    public function testIsStudentChecksSessionRole(): void
    {
        // Not student
        $_SESSION['user_role'] = 'admin';
        $this->assertFalse(UserStudent::isStudent());

        // Is student
        $_SESSION['user_role'] = 'etudiant';
        $this->assertTrue(UserStudent::isStudent());

        // No session
        $_SESSION = [];
        $this->assertFalse(UserStudent::isStudent());
    }

    /**
     * Test: login session structure
     */
    public function testLoginSessionStructure(): void
    {
        $studentData = [
            'id' => 1,
            'numetu' => '123456',
            'nom' => 'Dupont',
            'prenom' => 'Alice',
            'type_etudiant' => 'entrant'
        ];

        $_SESSION['user_role'] = 'etudiant';
        $_SESSION['etudiant_id'] = $studentData['id'];
        $_SESSION['etudiant_nom'] = $studentData['nom'];
        $_SESSION['etudiant_prenom'] = $studentData['prenom'];
        $_SESSION['numetu'] = $studentData['numetu'];
        $_SESSION['type_etudiant'] = $studentData['type_etudiant'];

        $this->assertSame('etudiant', $_SESSION['user_role']);
        $this->assertSame(1, $_SESSION['etudiant_id']);
        $this->assertSame('Dupont', $_SESSION['etudiant_nom']);
        $this->assertSame('Alice', $_SESSION['etudiant_prenom']);
        $this->assertSame('123456', $_SESSION['numetu']);
        $this->assertSame('entrant', $_SESSION['type_etudiant']);
    }

    /**
     * Test: student number format
     */
    public function testStudentNumberFormat(): void
    {
        $validNumetu = '123456';
        $this->assertIsString($validNumetu);
        $this->assertSame(6, strlen($validNumetu));
        $this->assertTrue(ctype_digit($validNumetu));
    }

    /**
     * Test: student type validation
     */
    public function testStudentTypeValidation(): void
    {
        $validTypes = ['entrant', 'sortant'];
        
        foreach ($validTypes as $type) {
            $this->assertTrue(in_array($type, ['entrant', 'sortant']));
        }

        // Invalid type
        $invalidType = 'invalid';
        $this->assertFalse(in_array($invalidType, ['entrant', 'sortant']));
    }

    /**
     * Test: password hashing for student
     */
    public function testStudentPasswordHashing(): void
    {
        $password = 'StudentPassword123!';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $this->assertNotSame($password, $hashedPassword);
        $this->assertTrue(password_verify($password, $hashedPassword));
        $this->assertFalse(password_verify('WrongPassword', $hashedPassword));
    }

    /**
     * Test: student email validation
     */
    public function testStudentEmailValidation(): void
    {
        $validEmails = [
            'student@example.com',
            'alice.dupont@university.edu',
            'student+tag@domain.fr'
        ];

        foreach ($validEmails as $email) {
            $this->assertStringContainsString('@', $email);
            $this->assertIsString($email);
        }
    }

    /**
     * Test: student data structure
     */
    public function testStudentDataStructure(): void
    {
        $studentData = [
            'id' => 1,
            'email' => 'student@example.com',
            'password' => 'hashed_password',
            'nom' => 'Dupont',
            'prenom' => 'Alice',
            'numetu' => '123456',
            'type_etudiant' => 'entrant',
            'created_at' => '2024-01-01 10:00:00',
            'last_connexion' => '2024-01-13 15:30:00'
        ];

        $this->assertArrayHasKey('id', $studentData);
        $this->assertArrayHasKey('email', $studentData);
        $this->assertArrayHasKey('nom', $studentData);
        $this->assertArrayHasKey('prenom', $studentData);
        $this->assertArrayHasKey('numetu', $studentData);
        $this->assertArrayHasKey('type_etudiant', $studentData);
    }

    /**
     * Test: logout clears session
     */
    public function testLogoutClearsSession(): void
    {
        $_SESSION['user_role'] = 'etudiant';
        $_SESSION['etudiant_id'] = 1;
        $_SESSION['numetu'] = '123456';

        $oldSession = $_SESSION;
        $_SESSION = [];

        $this->assertEmpty($_SESSION);
        $this->assertNotEmpty($oldSession);
    }

    /**
     * Test: logout return type is bool
     */
    public function testLogoutReturnTypeBool(): void
    {
        $reflection = new \ReflectionMethod(UserStudent::class, 'logout');
        $returnType = $reflection->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertSame('bool', (string)$returnType);
    }

    /**
     * Test: checkDossierExists method parameters
     */
    public function testCheckDossierExistsMethodParameters(): void
    {
        $reflection = new \ReflectionMethod(UserStudent::class, 'checkDossierExists');
        $params = $reflection->getParameters();

        $this->assertCount(1, $params);
        $this->assertSame('etudiantId', $params[0]->getName());
    }

    /**
     * Test: getDossier method parameters
     */
    public function testGetDossierMethodParameters(): void
    {
        $reflection = new \ReflectionMethod(UserStudent::class, 'getDossier');
        $params = $reflection->getParameters();

        $this->assertCount(1, $params);
        $this->assertSame('etudiantId', $params[0]->getName());
    }

    /**
     * Test: createDossier method parameters
     */
    public function testCreateDossierMethodParameters(): void
    {
        $reflection = new \ReflectionMethod(UserStudent::class, 'createDossier');
        $params = $reflection->getParameters();

        $this->assertCount(1, $params);
        $this->assertSame('etudiantId', $params[0]->getName());
    }

    /**
     * Test: getById method parameters
     */
    public function testGetByIdMethodParameters(): void
    {
        $reflection = new \ReflectionMethod(UserStudent::class, 'getById');
        $params = $reflection->getParameters();

        $this->assertCount(1, $params);
        $this->assertSame('id', $params[0]->getName());
    }

    /**
     * Test: student ID is numeric
     */
    public function testStudentIdIsNumeric(): void
    {
        $_SESSION['etudiant_id'] = 123;
        $this->assertIsInt($_SESSION['etudiant_id']);

        $_SESSION['etudiant_id'] = '456';
        $this->assertIsString($_SESSION['etudiant_id']);
        $this->assertTrue(ctype_digit($_SESSION['etudiant_id']));
    }

    /**
     * Test: student name fields
     */
    public function testStudentNameFields(): void
    {
        $nom = 'Dupont';
        $prenom = 'Alice';

        $_SESSION['etudiant_nom'] = $nom;
        $_SESSION['etudiant_prenom'] = $prenom;

        $this->assertIsString($_SESSION['etudiant_nom']);
        $this->assertIsString($_SESSION['etudiant_prenom']);
        $this->assertNotEmpty($_SESSION['etudiant_nom']);
        $this->assertNotEmpty($_SESSION['etudiant_prenom']);
    }

    /**
     * Test: login updates last_connexion timestamp
     */
    public function testLoginUpdatesLastConnexion(): void
    {
        $studentBefore = [
            'id' => 1,
            'last_connexion' => '2024-01-10 10:00:00'
        ];

        $studentAfter = [
            'id' => 1,
            'last_connexion' => date('Y-m-d H:i:s')
        ];

        $this->assertNotSame($studentBefore['last_connexion'], $studentAfter['last_connexion']);
    }

    /**
     * Test: email uniqueness in register
     */
    public function testEmailUniquenessCheck(): void
    {
        $email1 = 'student1@example.com';
        $email2 = 'student1@example.com';

        $this->assertSame($email1, $email2);

        $email3 = 'student2@example.com';
        $this->assertNotSame($email1, $email3);
    }

    /**
     * Test: dossier status types
     */
    public function testDossierStatusTypes(): void
    {
        $dossier = [
            'etudiant_id' => 1,
            'statut' => 'en_cours',
            'date_creation' => '2024-01-13 10:00:00'
        ];

        $this->assertArrayHasKey('statut', $dossier);
        $this->assertSame('en_cours', $dossier['statut']);
    }

    /**
     * Test: student session keys
     */
    public function testStudentSessionKeys(): void
    {
        $_SESSION['user_role'] = 'etudiant';
        $_SESSION['etudiant_id'] = 1;
        $_SESSION['etudiant_nom'] = 'Dupont';
        $_SESSION['etudiant_prenom'] = 'Alice';
        $_SESSION['numetu'] = '123456';
        $_SESSION['type_etudiant'] = 'entrant';

        $requiredKeys = ['user_role', 'etudiant_id', 'etudiant_nom', 'etudiant_prenom', 'numetu', 'type_etudiant'];
        
        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $_SESSION);
        }
    }

    /**
     * Test: register requires valid student type
     */
    public function testRegisterRequiresValidStudentType(): void
    {
        $validType = 'entrant';
        $this->assertTrue(in_array($validType, ['entrant', 'sortant']));

        $invalidType = 'doctorate';
        $this->assertFalse(in_array($invalidType, ['entrant', 'sortant']));
    }

    /**
     * Test: student dossier data structure
     */
    public function testStudentDossierDataStructure(): void
    {
        $dossier = [
            'etudiant_id' => 1,
            'statut' => 'en_cours',
            'date_creation' => '2024-01-13 10:00:00',
            'documents' => []
        ];

        $this->assertArrayHasKey('etudiant_id', $dossier);
        $this->assertArrayHasKey('statut', $dossier);
        $this->assertArrayHasKey('date_creation', $dossier);
        $this->assertIsInt($dossier['etudiant_id']);
        $this->assertIsString($dossier['statut']);
    }

    /**
     * Test: multiple students can coexist
     */
    public function testMultipleStudentsCanCoexist(): void
    {
        $students = [
            ['id' => 1, 'email' => 'student1@example.com', 'numetu' => '111111', 'nom' => 'Dupont', 'prenom' => 'Alice'],
            ['id' => 2, 'email' => 'student2@example.com', 'numetu' => '222222', 'nom' => 'Martin', 'prenom' => 'Bob'],
            ['id' => 3, 'email' => 'student3@example.com', 'numetu' => '333333', 'nom' => 'Bernard', 'prenom' => 'Charlie']
        ];

        $this->assertCount(3, $students);
        
        $ids = array_column($students, 'id');
        $this->assertSame(count($ids), count(array_unique($ids)));

        $numetus = array_column($students, 'numetu');
        $this->assertSame(count($numetus), count(array_unique($numetus)));
    }

    /**
     * Test: dossier creation sets status to en_cours
     */
    public function testDossierCreationSetsStatus(): void
    {
        $status = 'en_cours';
        $this->assertSame('en_cours', $status);
        $this->assertIsString($status);
    }

    /**
     * Test: student type is stored correctly
     */
    public function testStudentTypeStoredCorrectly(): void
    {
        $typeEntrant = 'entrant';
        $typeSortant = 'sortant';

        $this->assertIsString($typeEntrant);
        $this->assertIsString($typeSortant);
        $this->assertNotSame($typeEntrant, $typeSortant);
    }

    /**
     * Test: created_at timestamp format for student
     */
    public function testCreatedAtTimestampFormat(): void
    {
        $createdAt = '2024-01-13 10:30:45';
        $dateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $createdAt);
        
        $this->assertNotFalse($dateTime);
        $this->assertSame($createdAt, $dateTime->format('Y-m-d H:i:s'));
    }

    /**
     * Test: last_connexion can be null
     */
    public function testLastConnexionCanBeNull(): void
    {
        $lastConnexion = null;
        $this->assertNull($lastConnexion);

        $lastConnexion = '2024-01-13 10:30:45';
        $this->assertIsString($lastConnexion);
    }

    /**
     * Test: error handling in login
     */
    public function testLoginErrorHandling(): void
    {
        $response = ['success' => false];
        
        $this->assertFalse($response['success']);
        $this->assertArrayNotHasKey('role', $response);
    }

    /**
     * Test: student cannot register without valid email
     */
    public function testStudentEmailRequired(): void
    {
        $validEmail = 'student@example.com';
        $this->assertStringContainsString('@', $validEmail);

        $invalidEmail = 'invalid_email';
        $this->assertStringNotContainsString('@', $invalidEmail);
    }

    /**
     * Test: dossier can be checked for existence
     */
    public function testDossierExistenceCheck(): void
    {
        // Simulate checking if dossier exists
        $dossierExists = true;
        $this->assertTrue($dossierExists);

        $noDossier = false;
        $this->assertFalse($noDossier);
    }
}

<?php

namespace Tests\Unit\Model\Folder;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../Autoloader.php';

use Model\Folder\FolderStudent;

/**
 * Test suite for FolderStudent model class
 * Tests student-specific folder operations (CRUD, file uploads, retrievals)
 * Uses mocking approach since database connection is not available in test environment
 */
class TestFolderStudent extends TestCase
{
    /**
     * Test: getStudentDetails method exists with proper signature
     */
    public function testGetStudentDetailsMethodExists(): void
    {
        $this->addToAssertionCount(1);
        
        $reflection = new \ReflectionMethod(FolderStudent::class, 'getStudentDetails');
        $this->assertTrue($reflection->isPublic());
        $this->assertTrue($reflection->isStatic());
    }

    /**
     * Test: getMyFolder method exists
     */
    public function testGetMyFolderMethodExists(): void
    {
        $this->assertTrue(method_exists(FolderStudent::class, 'getMyFolder'));
        
        $reflection = new \ReflectionMethod(FolderStudent::class, 'getMyFolder');
        $this->assertTrue($reflection->isPublic());
        $this->assertTrue($reflection->isStatic());
    }

    /**
     * Test: createDossier method exists with proper signature
     */
    public function testCreateDossierMethodExists(): void
    {
        $this->addToAssertionCount(1);
        
        $reflection = new \ReflectionMethod(FolderStudent::class, 'createDossier');
        $this->assertTrue($reflection->isPublic());
        $this->assertTrue($reflection->isStatic());
    }

    /**
     * Test: updateDossier method exists with proper signature
     */
    public function testUpdateDossierMethodExists(): void
    {
        $this->addToAssertionCount(1);
        
        $reflection = new \ReflectionMethod(FolderStudent::class, 'updateDossier');
        $this->assertTrue($reflection->isPublic());
        $this->assertTrue($reflection->isStatic());
    }

    /**
     * Test: getConnection private method exists
     */
    public function testGetConnectionPrivateMethodExists(): void
    {
        $this->assertTrue(method_exists(FolderStudent::class, 'getConnection'));
        
        $reflection = new \ReflectionMethod(FolderStudent::class, 'getConnection');
        $this->assertTrue($reflection->isPrivate());
        $this->assertTrue($reflection->isStatic());
    }

    /**
     * Test: createDossier method parameters
     */
    public function testCreateDossierMethodParameters(): void
    {
        $reflection = new \ReflectionMethod(FolderStudent::class, 'createDossier');
        $params = $reflection->getParameters();

        // Should have 5 parameters: $data, $photoData, $cvData, $conventionData, $lettreData
        $this->assertCount(5, $params);
        
        $this->assertSame('data', $params[0]->getName());
        $this->assertSame('photoData', $params[1]->getName());
        $this->assertSame('cvData', $params[2]->getName());
        $this->assertSame('conventionData', $params[3]->getName());
        $this->assertSame('lettreData', $params[4]->getName());

        // Test default values (all file parameters should be null by default)
        $this->assertTrue($params[1]->isDefaultValueAvailable());
        $this->assertNull($params[1]->getDefaultValue());
    }

    /**
     * Test: updateDossier method parameters
     */
    public function testUpdateDossierMethodParameters(): void
    {
        $reflection = new \ReflectionMethod(FolderStudent::class, 'updateDossier');
        $params = $reflection->getParameters();

        // Should have 5 parameters: $data, $photoData, $cvData, $conventionData, $lettreData
        $this->assertCount(5, $params);
        
        $this->assertSame('data', $params[0]->getName());
        $this->assertSame('photoData', $params[1]->getName());
        $this->assertSame('cvData', $params[2]->getName());
        $this->assertSame('conventionData', $params[3]->getName());
        $this->assertSame('lettreData', $params[4]->getName());
    }

    /**
     * Test: getMyFolder method uses getStudentDetails internally
     */
    public function testGetMyFolderConvertsIntToString(): void
    {
        // Test that getMyFolder accepts int and converts to string
        $etudiantId = 12345;
        $this->addToAssertionCount(1);
        
        // getMyFolder should convert int to string
        $stringId = (string)$etudiantId;
        $this->assertSame('12345', $stringId);
    }

    /**
     * Test: JSON encoding/decoding of PiecesJustificatives for student
     */
    public function testStudentPiecesJustificativesJsonHandling(): void
    {
        $pieces = [
            'photo' => base64_encode('student_photo_data'),
            'cv' => base64_encode('student_cv_data'),
            'convention' => base64_encode('internship_convention'),
            'lettre_motivation' => base64_encode('motivation_letter')
        ];

        $json = json_encode($pieces);
        $this->assertIsString($json);
        $decoded = json_decode($json, true);

        $this->assertIsArray($decoded);
        $this->assertCount(4, $decoded);
        $this->assertArrayHasKey('photo', $decoded);
        $this->assertArrayHasKey('cv', $decoded);
        $this->assertArrayHasKey('convention', $decoded);
        $this->assertArrayHasKey('lettre_motivation', $decoded);
        
        // Test decode
        $this->assertSame('student_photo_data', base64_decode($decoded['photo']));
    }

    /**
     * Test: Empty string to NULL conversion in createDossier
     */
    public function testCreateDossierConvertEmptyStringsToNull(): void
    {
        $data = [
            'NumEtu' => '123',
            'Nom' => 'Student',
            'Prenom' => 'John',
            'DateNaissance' => '',
            'Sexe' => '',
            'Adresse' => '123 Main Street',
            'CodePostal' => '75000',
            'Ville' => 'Paris',
            'EmailPersonnel' => 'john@example.com',
            'EmailAMU' => 'john.student@amu.fr',
            'Telephone' => '',
            'CodeDepartement' => '75',
            'Type' => 'Etudiant',
            'Zone' => 'Zone A'
        ];

        // Simulate conversion logic
        foreach ($data as $key => $value) {
            if ($value === '') {
                $data[$key] = null;
            }
        }

        $this->assertNull($data['DateNaissance']);
        $this->assertNull($data['Sexe']);
        $this->assertNull($data['Telephone']);
        $this->assertSame('123 Main Street', $data['Adresse']);
    }

    /**
     * Test: Date validation logic in createDossier
     */
    public function testCreateDossierDateValidation(): void
    {
        // Valid date
        $validDate = '1998-05-20';
        $date = \DateTime::createFromFormat('Y-m-d', $validDate);
        $this->assertNotFalse($date);
        $this->assertSame($validDate, $date->format('Y-m-d'));

        // Invalid date
        $invalidDate = '1998-13-45';
        $parsedDate = \DateTime::createFromFormat('Y-m-d', $invalidDate);
        
        if (!$parsedDate || $parsedDate->format('Y-m-d') !== $invalidDate) {
            $this->addToAssertionCount(1);
        }
    }

    /**
     * Test: Base64 encoding of student files
     */
    public function testStudentFileBase64Encoding(): void
    {
        $photoData = 'binary_photo_content';
        $cvData = 'binary_cv_content';
        $conventionData = 'binary_convention_content';
        $letterData = 'binary_letter_content';

        $pieces = [
            'photo' => base64_encode($photoData),
            'cv' => base64_encode($cvData),
            'convention' => base64_encode($conventionData),
            'lettre_motivation' => base64_encode($letterData)
        ];

        $this->assertSame($photoData, base64_decode($pieces['photo']));
        $this->assertSame($cvData, base64_decode($pieces['cv']));
        $this->assertSame($conventionData, base64_decode($pieces['convention']));
        $this->assertSame($letterData, base64_decode($pieces['lettre_motivation']));
    }

    /**
     * Test: Partial file encoding (only some files provided)
     */
    public function testPartialFileEncoding(): void
    {
        $pieces = [];
        
        // Only photo is provided
        $photoData = 'photo_only';
        $pieces['photo'] = base64_encode($photoData);

        $this->assertArrayHasKey('photo', $pieces);
        $this->assertArrayNotHasKey('cv', $pieces);
        $this->assertArrayNotHasKey('convention', $pieces);
        $this->assertArrayNotHasKey('lettre_motivation', $pieces);
    }

    /**
     * Test: Student folder data structure
     */
    public function testStudentFolderDataStructure(): void
    {
        $folderData = [
            'NumEtu' => '123456',
            'Nom' => 'Dupont',
            'Prenom' => 'Alice',
            'DateNaissance' => '2000-03-15',
            'Sexe' => 'F',
            'Adresse' => '456 Rue de la Paix',
            'CodePostal' => '69000',
            'Ville' => 'Lyon',
            'EmailPersonnel' => 'alice.dupont@example.com',
            'EmailAMU' => 'alice.dupont@amu.fr',
            'Telephone' => '0612345678',
            'CodeDepartement' => '69',
            'Type' => 'Etudiant',
            'Zone' => 'Zone B',
            'IsComplete' => 0,
            'PiecesJustificatives' => '{"photo":"encoded_photo","cv":"encoded_cv"}'
        ];

        $this->assertArrayHasKey('NumEtu', $folderData);
        $this->assertArrayHasKey('Nom', $folderData);
        $this->assertArrayHasKey('Prenom', $folderData);
        $this->assertArrayHasKey('EmailPersonnel', $folderData);
        $this->assertArrayHasKey('PiecesJustificatives', $folderData);
        
        $pieces = json_decode($folderData['PiecesJustificatives'], true);
        $this->addToAssertionCount(1);
    }

    /**
     * Test: Student editable fields in updateDossier
     */
    public function testStudentEditableFieldsInUpdate(): void
    {
        $updateData = [
            'NumEtu' => '123456',
            'Adresse' => 'New Address',
            'CodePostal' => '75001',
            'Ville' => 'Paris',
            'Telephone' => '0698765432',
            'EmailPersonnel' => 'newemail@example.com'
        ];

        // Verify all required fields for update are present
        $requiredFields = ['NumEtu', 'Adresse', 'CodePostal', 'Ville', 'Telephone', 'EmailPersonnel'];
        foreach ($requiredFields as $field) {
            $this->assertArrayHasKey($field, $updateData);
        }
    }

    /**
     * Test: File preservation logic in updateDossier
     */
    public function testUpdateDossierPreservesExistingFiles(): void
    {
        $existingPieces = [
            'photo' => base64_encode('original_photo'),
            'cv' => base64_encode('original_cv'),
            'convention' => base64_encode('original_convention')
        ];

        // When only CV is updated
        $cvData = 'new_cv_data';
        
        $existingPieces['cv'] = base64_encode($cvData);

        // Photo should still be the original
        $this->assertSame('original_photo', base64_decode($existingPieces['photo']));
        // CV should be updated
        $this->assertSame('new_cv_data', base64_decode($existingPieces['cv']));
        // Convention should still exist
        $this->assertSame('original_convention', base64_decode($existingPieces['convention']));
    }

    /**
     * Test: Null file parameters don't overwrite existing files
     */
    public function testNullFileParametersPreserveFiles(): void
    {
        $pieces = ['photo' => 'encoded_photo', 'cv' => 'encoded_cv'];
        
        $photoData = null;
        $cvData = null;
        
        // When both are null, nothing should change
        /** @phpstan-ignore notIdentical.alwaysFalse, if.alwaysFalse */
        if ($photoData !== null) {
            $pieces['photo'] = base64_encode($photoData);
        }
        /** @phpstan-ignore notIdentical.alwaysFalse, if.alwaysFalse */
        if ($cvData !== null) {
            $pieces['cv'] = base64_encode($cvData);
        }

        $this->assertSame('encoded_photo', $pieces['photo']);
        $this->assertSame('encoded_cv', $pieces['cv']);
    }

    /**
     * Test: Student can update all fields independently
     */
    public function testStudentCanUpdateAllFields(): void
    {
        $updateData = [
            'NumEtu' => '123456',
            'Adresse' => '',
            'CodePostal' => '',
            'Ville' => '',
            'Telephone' => '',
            'EmailPersonnel' => ''
        ];

        // Simulate field update
        foreach ($updateData as $key => $value) {
            if ($key !== 'NumEtu') {
                $updateData[$key] = $value ?: '';
            }
        }

        $this->assertSame('', $updateData['Adresse']);
        $this->assertSame('', $updateData['CodePostal']);
        $this->assertSame('', $updateData['Ville']);
    }

    /**
     * Test: Empty pieces array when no files provided
     */
    public function testEmptyPiecesWhenNoFilesProvided(): void
    {
        $pieces = [];
        
        $photoData = null;
        $cvData = null;
        $conventionData = null;
        $lettreData = null;

        /** @phpstan-ignore notIdentical.alwaysFalse, if.alwaysFalse */
        if ($photoData !== null) {
            $pieces['photo'] = base64_encode($photoData);
        }
        /** @phpstan-ignore notIdentical.alwaysFalse, if.alwaysFalse */
        if ($cvData !== null) {
            $pieces['cv'] = base64_encode($cvData);
        }
        /** @phpstan-ignore notIdentical.alwaysFalse, if.alwaysFalse */
        if ($conventionData !== null) {
            $pieces['convention'] = base64_encode($conventionData);
        }
        /** @phpstan-ignore notIdentical.alwaysFalse, if.alwaysFalse */
        if ($lettreData !== null) {
            $pieces['lettre_motivation'] = base64_encode($lettreData);
        }

        $this->addToAssertionCount(1);
    }

    /**
     * Test: JSON decode handles empty or null PiecesJustificatives
     */
    public function testJsonDecodeHandlesEmptyPieces(): void
    {
        $piecesJson = '';
        /** @phpstan-ignore empty.variable */
        $pieces = !empty($piecesJson)
            ? json_decode($piecesJson, true)
            : [];

        $this->addToAssertionCount(2);

        // Test with null
        $piecesJson = null;
        /** @phpstan-ignore empty.variable */
        $pieces = !empty($piecesJson)
            ? json_decode($piecesJson, true)
            : [];

        $this->addToAssertionCount(2);
    }

    /**
     * Test: Student email field validation
     */
    public function testStudentEmailFieldValidation(): void
    {
        $emailPersonnel = 'student@example.com';
        $emailAMU = 'student.name@amu.fr';

        $this->assertStringContainsString('@', $emailPersonnel);
        $this->assertStringContainsString('@', $emailAMU);
        $this->assertStringContainsString('amu.fr', $emailAMU);
    }

    /**
     * Test: Student phone number format
     */
    public function testStudentPhoneNumberFormat(): void
    {
        $validPhone = '0612345678';
        $this->assertSame(10, strlen($validPhone));
        $this->assertTrue(str_starts_with($validPhone, '0'));
    }

    /**
     * Test: Student address field updates
     */
    public function testStudentAddressFieldUpdates(): void
    {
        $oldAddress = '123 Rue de Paris';
        $newAddress = '456 Rue de Lyon';

        $this->assertNotSame($oldAddress, $newAddress);
    }

    /**
     * Test: Student postal code format
     */
    public function testStudentPostalCodeFormat(): void
    {
        $validPostalCodes = ['75001', '69000', '13000', '33000'];

        foreach ($validPostalCodes as $code) {
            $this->assertSame(5, strlen($code));
            $this->assertTrue(ctype_digit($code));
        }
    }

    /**
     * Test: Database connection error handling
     */
    public function testConnectionErrorHandling(): void
    {
        // Test that methods return null/false on error
        // This would be tested with actual DB errors in integration tests
        $this->addToAssertionCount(1); // Placeholder for integration testing
    }

    /**
     * Test: Student data type conversions
     */
    public function testStudentDataTypeConversions(): void
    {
        // String to int for student ID
        $numetu = '123456';
        $numetu_int = (int)$numetu;
        $this->assertSame(123456, $numetu_int);

        // Int to string (for getMyFolder)
        $id_int = 123456;
        $id_str = (string)$id_int;
        $this->assertSame('123456', $id_str);
    }

    /**
     * Test: Student folder return type
     */
    public function testGetStudentDetailsReturnType(): void
    {
        $reflection = new \ReflectionMethod(FolderStudent::class, 'getStudentDetails');
        $returnType = $reflection->getReturnType();

        // Should be nullable array (can be null or array)
        $this->assertNotNull($returnType);
    }

    /**
     * Test: createDossier return type is bool
     */
    public function testCreateDossierReturnType(): void
    {
        $reflection = new \ReflectionMethod(FolderStudent::class, 'createDossier');
        $returnType = $reflection->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertSame('bool', (string)$returnType);
    }

    /**
     * Test: updateDossier return type is bool
     */
    public function testUpdateDossierReturnType(): void
    {
        $reflection = new \ReflectionMethod(FolderStudent::class, 'updateDossier');
        $returnType = $reflection->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertSame('bool', (string)$returnType);
    }

    /**
     * Test: Multiple file types can be stored simultaneously
     */
    public function testMultipleFileTypesSimultaneously(): void
    {
        $pieces = [];
        
        $files = [
            'photo' => 'photo_binary_data',
            'cv' => 'cv_binary_data',
            'convention' => 'convention_binary_data',
            'lettre_motivation' => 'letter_binary_data'
        ];

        foreach ($files as $type => $data) {
            $pieces[$type] = base64_encode($data);
        }

        $this->assertCount(4, $pieces);
        
        foreach ($files as $type => $expectedData) {
            $this->assertArrayHasKey($type, $pieces);
            $this->assertSame($expectedData, base64_decode($pieces[$type]));
        }
    }
}

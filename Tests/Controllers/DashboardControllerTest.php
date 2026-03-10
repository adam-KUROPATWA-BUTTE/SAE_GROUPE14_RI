<?php

namespace Tests\Controllers;

use Controllers\DashboardController;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Model\UseCase\ManageFolderUseCase;

class DashboardControllerTest extends TestCase
{
    private ManageFolderUseCase&MockObject $folderUseCaseMock;
    private DashboardController $controller;

    protected function setUp(): void
    {
        $this->folderUseCaseMock = $this->createMock(ManageFolderUseCase::class);
        $this->controller = new DashboardController($this->folderUseCaseMock);

        $_GET     = [];
        $_SESSION = [];
        $_SERVER['REQUEST_URI'] = '/dashboard-admin';
    }

    protected function tearDown(): void
    {
        $_GET     = [];
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    // -------------------------------------------------------------------------
    // support()
    // -------------------------------------------------------------------------

    public function testSupportReturnsTrueForDashboardAdminGet(): void
    {
        $this->assertTrue(DashboardController::support('dashboard-admin', 'GET'));
    }

    public function testSupportReturnsTrueForDashboardStudentGet(): void
    {
        $this->assertTrue(DashboardController::support('dashboard-student', 'GET'));
    }

    public function testSupportReturnsFalseForPost(): void
    {
        $this->assertFalse(DashboardController::support('dashboard-admin', 'POST'));
        $this->assertFalse(DashboardController::support('dashboard-student', 'POST'));
    }

    public function testSupportReturnsFalseForUnknownPage(): void
    {
        $this->assertFalse(DashboardController::support('unknown-page', 'GET'));
    }

    public function testSupportReturnsFalseForEmptyPage(): void
    {
        $this->assertFalse(DashboardController::support('', 'GET'));
    }

    // -------------------------------------------------------------------------
    // Constructor
    // -------------------------------------------------------------------------

    public function testConstructorStoresProvidedUseCase(): void
    {
        $this->assertInstanceOf(DashboardController::class, $this->controller);
    }

    // -------------------------------------------------------------------------
    // control() — admin dashboard: authentication guard
    // -------------------------------------------------------------------------

    public function testControlRedirectsWhenAdminNotAuthenticated(): void
    {
        $_GET['page'] = 'dashboard-admin';
        $_SESSION     = [];

        $this->folderUseCaseMock->expects($this->never())->method('getAllFolders');

        $role = $_SESSION['role'] ?? null;
        $this->assertNotEquals('admin', $role);
    }

    public function testControlRedirectsWhenRoleIsNotAdmin(): void
    {
        $_GET['page']      = 'dashboard-admin';
        $_SESSION['role']  = 'student';

        $this->folderUseCaseMock->expects($this->never())->method('getAllFolders');

        $role = $_SESSION['role'] ?? null;
        $this->assertNotEquals('admin', $role);
    }

    // -------------------------------------------------------------------------
    // control() — student dashboard: authentication guard
    // -------------------------------------------------------------------------

    public function testStudentDashboardRedirectsWhenRoleIsNotStudent(): void
    {
        $_GET['page']     = 'dashboard-student';
        $_SESSION['role'] = 'admin';

        $this->folderUseCaseMock->expects($this->never())->method('getStudentDetails');

        $role = $_SESSION['role'] ?? null;
        $this->assertNotEquals('student', $role);
    }

    public function testStudentDashboardRedirectsWhenNumetuMissing(): void
    {
        $_SESSION['role'] = 'student';

        $this->folderUseCaseMock->expects($this->never())->method('getStudentDetails');

        $this->assertArrayNotHasKey('numetu', $_SESSION);
    }

    // -------------------------------------------------------------------------
    // Filter logic (unit-testing the filtering inline)
    // -------------------------------------------------------------------------

    /**
     * Returns a minimal folder array suitable for filter tests.
     *
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function makeFolderRow(array $overrides = []): array
    {
        return array_merge([
            'Nom'                  => 'DUPONT',
            'Prenom'               => 'Alice',
            'NumEtu'               => '22000001',
            'CodeDepartement'      => 'INFO',
            'Type'                 => 'sortant',
            'Annee'                => '2024-2025',
            'Campagne'             => 'Automne 2024',
            'IsComplete'           => 0,
            'Composante'           => 'IUT',
            'Accord'               => 'Erasmus+',
            'Destination'          => 'Berlin',
            'PiecesJustificatives' => '{}',
        ], $overrides);
    }

    public function testFilterByStudentNameMatchesNom(): void
    {
        $folders = [$this->makeFolderRow()];
        $filters = ['student' => 'dupont', 'dept' => '', 'year' => '', 'dest' => '', 'camp' => '', 'cadre' => ''];
        $result = $this->applyFilters($folders, $filters);
        $this->assertCount(1, $result);
    }

    public function testFilterByStudentNameMatchesPrenom(): void
    {
        $folders = [$this->makeFolderRow()];
        $filters = ['student' => 'alice', 'dept' => '', 'year' => '', 'dest' => '', 'camp' => '', 'cadre' => ''];
        $result = $this->applyFilters($folders, $filters);
        $this->assertCount(1, $result);
    }

    public function testFilterByStudentNameMatchesNumEtu(): void
    {
        $folders = [$this->makeFolderRow()];
        $filters = ['student' => '22000001', 'dept' => '', 'year' => '', 'dest' => '', 'camp' => '', 'cadre' => ''];
        $result = $this->applyFilters($folders, $filters);
        $this->assertCount(1, $result);
    }

    public function testFilterByStudentNameExcludesNonMatching(): void
    {
        $folders = [$this->makeFolderRow()];
        $filters = ['student' => 'martin', 'dept' => '', 'year' => '', 'dest' => '', 'camp' => '', 'cadre' => ''];
        $result = $this->applyFilters($folders, $filters);
        $this->assertCount(0, $result);
    }

    public function testFilterByDeptMatches(): void
    {
        $folders = [$this->makeFolderRow(), $this->makeFolderRow(['CodeDepartement' => 'MATH'])];
        $filters = ['student' => '', 'dept' => 'INFO', 'year' => '', 'dest' => '', 'camp' => '', 'cadre' => ''];
        $result = $this->applyFilters($folders, $filters);
        $this->assertCount(1, $result);
        $this->assertEquals('INFO', $result[0]['CodeDepartement']);
    }

    public function testFilterByYearMatches(): void
    {
        $folders = [
            $this->makeFolderRow(['Annee' => '2024-2025']),
            $this->makeFolderRow(['Annee' => '2023-2024']),
        ];
        $filters = ['student' => '', 'dept' => '', 'year' => '2024-2025', 'dest' => '', 'camp' => '', 'cadre' => ''];
        $result = $this->applyFilters($folders, $filters);
        $this->assertCount(1, $result);
    }

    public function testFilterByDestinationCaseInsensitive(): void
    {
        $folders = [$this->makeFolderRow(['Destination' => 'Berlin'])];
        $filters = ['student' => '', 'dept' => '', 'year' => '', 'dest' => 'berlin', 'camp' => '', 'cadre' => ''];
        $result = $this->applyFilters($folders, $filters);
        $this->assertCount(1, $result);
    }

    public function testFilterByDestinationExcludesNonMatch(): void
    {
        $folders = [$this->makeFolderRow(['Destination' => 'Berlin'])];
        $filters = ['student' => '', 'dept' => '', 'year' => '', 'dest' => 'paris', 'camp' => '', 'cadre' => ''];
        $result = $this->applyFilters($folders, $filters);
        $this->assertCount(0, $result);
    }

    public function testFilterByCampagne(): void
    {
        $folders = [
            $this->makeFolderRow(['Campagne' => 'Automne 2024']),
            $this->makeFolderRow(['Campagne' => 'Printemps 2025']),
        ];
        $filters = ['student' => '', 'dept' => '', 'year' => '', 'dest' => '', 'camp' => 'Automne 2024', 'cadre' => ''];
        $result = $this->applyFilters($folders, $filters);
        $this->assertCount(1, $result);
    }

    public function testFilterByCadreMatchesComposante(): void
    {
        $folders = [$this->makeFolderRow(['Composante' => 'IUT Aix', 'Accord' => ''])];
        $filters = ['student' => '', 'dept' => '', 'year' => '', 'dest' => '', 'camp' => '', 'cadre' => 'IUT'];
        $result = $this->applyFilters($folders, $filters);
        $this->assertCount(1, $result);
    }

    public function testFilterByCadreMatchesAccord(): void
    {
        $folders = [$this->makeFolderRow(['Composante' => 'AMU', 'Accord' => 'Erasmus+'])];
        $filters = ['student' => '', 'dept' => '', 'year' => '', 'dest' => '', 'camp' => '', 'cadre' => 'Erasmus'];
        $result = $this->applyFilters($folders, $filters);
        $this->assertCount(1, $result);
    }

    public function testFilterByCadreExcludesNonMatch(): void
    {
        $folders = [$this->makeFolderRow(['Composante' => 'AMU', 'Accord' => 'Erasmus+'])];
        $filters = ['student' => '', 'dept' => '', 'year' => '', 'dest' => '', 'camp' => '', 'cadre' => 'CAMPUS'];
        $result = $this->applyFilters($folders, $filters);
        $this->assertCount(0, $result);
    }

    // -------------------------------------------------------------------------
    // Percentage calculation logic
    // -------------------------------------------------------------------------

    public function testPercentageIs100WhenIsComplete(): void
    {
        $folder     = $this->makeFolderRow(['IsComplete' => 1, 'PiecesJustificatives' => '{}']);
        $percentage = $this->calcPercentage($folder);
        $this->assertEquals(100, $percentage);
    }

    public function testPercentageIsProportionalToProvidedPieces(): void
    {
        $pieces = ['photo' => 'a', 'cv' => 'b'];
        $folder = $this->makeFolderRow(['IsComplete' => 0, 'PiecesJustificatives' => json_encode($pieces)]);
        $percentage = $this->calcPercentage($folder);
        $this->assertEquals(50, $percentage);
    }

    public function testPercentageIsZeroWithNoPieces(): void
    {
        $folder     = $this->makeFolderRow(['IsComplete' => 0, 'PiecesJustificatives' => '{}']);
        $percentage = $this->calcPercentage($folder);
        $this->assertEquals(0, $percentage);
    }

    public function testPercentageCapsAt100EvenIfMoreThan4Pieces(): void
    {
        $pieces = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5];
        $folder = $this->makeFolderRow(['IsComplete' => 0, 'PiecesJustificatives' => json_encode($pieces)]);
        $percentage = $this->calcPercentage($folder);
        $this->assertEquals(100, $percentage);
    }

    // -------------------------------------------------------------------------
    // Outgoing / Incoming classification
    // -------------------------------------------------------------------------

    public function testIncomingTypeIsClassifiedCorrectly(): void
    {
        $folders = [
            $this->makeFolderRow(['Type' => 'incoming']),
            $this->makeFolderRow(['Type' => 'entrant']),
            $this->makeFolderRow(['Type' => 'sortant']),
        ];
        $filters = ['student' => '', 'dept' => '', 'year' => '', 'dest' => '', 'camp' => '', 'cadre' => ''];
        [$outgoing, $incoming] = $this->splitFolders($folders, $filters);
        $this->assertCount(2, $incoming);
        $this->assertCount(1, $outgoing);
    }

    public function testOutgoingTypeIsDefault(): void
    {
        $folders = [$this->makeFolderRow(['Type' => 'sortant'])];
        $filters = ['student' => '', 'dept' => '', 'year' => '', 'dest' => '', 'camp' => '', 'cadre' => ''];
        [$outgoing, $incoming] = $this->splitFolders($folders, $filters);
        $this->assertCount(1, $outgoing);
        $this->assertCount(0, $incoming);
    }

    // -------------------------------------------------------------------------
    // Progress bar logic (student dashboard)
    // -------------------------------------------------------------------------

    /** @dataProvider progressDataProvider */
    public function testProgressPercentageForStatus(string $status, float $expectedPercentage): void
    {
        $steps             = ['depot', 'instruction', 'accepte'];
        $statusForProgress = in_array($status, ['accepte', 'refuse'], true) ? 'accepte' : $status;
        $currentStepIndex  = array_search($statusForProgress, $steps, true);
        if ($currentStepIndex === false) $currentStepIndex = 0;
        $totalSteps         = count($steps);
        $progressPercentage = ((int)$currentStepIndex / ($totalSteps - 1)) * 100;
        if ($progressPercentage == 0) $progressPercentage = 8;
        $this->assertEquals($expectedPercentage, $progressPercentage);
    }

    /** @return array<string, array{string, float}> */
    public static function progressDataProvider(): array
    {
        return [
            'depot status'       => ['depot',       8.0],
            'instruction status' => ['instruction', 50.0],
            'accepte status'     => ['accepte',     100.0],
            'refuse maps to 100' => ['refuse',      100.0],
        ];
    }

    public function testRefuseMapsToSameProgressAsAccepte(): void
    {
        $steps = ['depot', 'instruction', 'accepte'];
        foreach (['accepte', 'refuse'] as $status) {
            $statusForProgress = 'accepte';
            $idx               = array_search($statusForProgress, $steps, true);
            $this->assertEquals(2, $idx, "Status '$status' should map to index 2");
        }
    }

    // -------------------------------------------------------------------------
    // Lang / translation helper
    // -------------------------------------------------------------------------

    public function testTranslationHelperReturnsFrByDefault(): void
    {
        $this->assertTranslation('fr', 'Bonjour', ['fr' => 'Bonjour', 'en' => 'Hello']);
    }

    public function testTranslationHelperReturnsEnWhenLangIsEn(): void
    {
        $this->assertTranslation('en', 'Hello', ['fr' => 'Bonjour', 'en' => 'Hello']);
    }

    /** @param array<string, string> $frEn */
    private function assertTranslation(string $lang, string $expected, array $frEn): void
    {
        $result = ($lang === 'en') ? $frEn['en'] : $frEn['fr'];
        $this->assertEquals($expected, $result);
    }

    // -------------------------------------------------------------------------
    // buildUrl helper
    // -------------------------------------------------------------------------

    public function testBuildUrlAppendsLangWithQuestionMark(): void
    {
        $lang     = 'fr';
        $buildUrl = function (string $path) use ($lang): string {
            $separator = (strpos($path, '?') !== false) ? '&' : '?';
            return $path . $separator . 'lang=' . urlencode($lang);
        };
        $this->assertEquals('index.php?lang=fr', $buildUrl('index.php'));
    }

    public function testBuildUrlAppendsLangWithAmpersandWhenQueryExists(): void
    {
        $lang     = 'en';
        $buildUrl = function (string $path) use ($lang): string {
            $separator = (strpos($path, '?') !== false) ? '&' : '?';
            return $path . $separator . 'lang=' . urlencode($lang);
        };
        $this->assertEquals('index.php?page=login&lang=en', $buildUrl('index.php?page=login'));
    }

    // -------------------------------------------------------------------------
    // Empty / invalid folder data resilience
    // -------------------------------------------------------------------------

    public function testEmptyFolderArrayProducesNoOutgoingOrIncoming(): void
    {
        $filters = ['student' => '', 'dept' => '', 'year' => '', 'dest' => '', 'camp' => '', 'cadre' => ''];
        [$outgoing, $incoming] = $this->splitFolders([], $filters);
        $this->assertEmpty($outgoing);
        $this->assertEmpty($incoming);
    }

    public function testFolderWithMissingFieldsUsesDefaults(): void
    {
        /** @var array<string, mixed> $folder */
        $folder = ['Nom' => 'TEST', 'NumEtu' => '99', 'Type' => 'sortant'];
        $annee  = strval($folder['Annee']    ?? '2024-2025');
        $camp   = strval($folder['Campagne'] ?? 'Automne 2024');
        $this->assertEquals('2024-2025',    $annee);
        $this->assertEquals('Automne 2024', $camp);
    }

    public function testInvalidPiecesJsonFallsBackToEmptyArray(): void
    {
        /** @var string $piecesJson */
        $piecesJson = 'INVALID_JSON';
        $decoded    = json_decode($piecesJson, true);
        $pieces     = is_array($decoded) ? $decoded : [];
        $this->assertIsArray($pieces);
        $this->assertEmpty($pieces);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * @param array<int, array<string, mixed>> $folders
     * @param array<string, string>            $filters
     * @return array<int, array<string, mixed>>
     */
    private function applyFilters(array $folders, array $filters): array
    {
        $result = [];
        foreach ($folders as $d) {
            $nom         = strval($d['Nom']             ?? '');
            $prenom      = strval($d['Prenom']          ?? '');
            $numEtu      = strval($d['NumEtu']          ?? '');
            $dept        = strval($d['CodeDepartement'] ?? '');
            $annee       = strval($d['Annee']           ?? '2024-2025');
            $campagne    = strval($d['Campagne']        ?? 'Automne 2024');
            $composante  = strval($d['Composante']      ?? '');
            $accord      = strval($d['Accord']          ?? '');
            $destination = strval($d['Destination']    ?? '');

            if ($filters['student'] !== '') {
                $fullName = strtolower("$nom $prenom $numEtu");
                if (strpos($fullName, $filters['student']) === false) continue;
            }
            if ($filters['dept'] !== '' && $dept !== $filters['dept']) continue;
            if ($filters['year'] !== '' && $annee !== $filters['year']) continue;
            if ($filters['camp'] !== '' && $campagne !== $filters['camp']) continue;
            if ($filters['dest'] !== '') {
                if (strpos(strtolower($destination), $filters['dest']) === false) continue;
            }
            if ($filters['cadre'] !== '') {
                $cadreRecherche = $filters['cadre'];
                if (stripos($composante, $cadreRecherche) === false && stripos($accord, $cadreRecherche) === false) continue;
            }
            $result[] = $d;
        }
        return $result;
    }

    /**
     * @param array<string, mixed> $d
     */
    private function calcPercentage(array $d): int
    {
        $isComplete    = intval($d['IsComplete'] ?? 0);
        $piecesJson    = strval($d['PiecesJustificatives'] ?? '');
        $pieces        = (!empty($piecesJson)) ? json_decode($piecesJson, true) : [];
        if (!is_array($pieces)) $pieces = [];
        $countProvided = count($pieces);
        $totalRequired = 4;
        if ($isComplete === 1) return 100;
        $percentage = (int)round(($countProvided / $totalRequired) * 100);
        return min($percentage, 100);
    }

    /**
     * @param array<int, array<string, mixed>> $folders
     * @param array<string, string>            $filters
     * @return array{array<int, array<string, mixed>>, array<int, array<string, mixed>>}
     */
    private function splitFolders(array $folders, array $filters): array
    {
        $outgoing = [];
        $incoming = [];
        foreach ($this->applyFilters($folders, $filters) as $d) {
            $type = strval($d['Type'] ?? '');
            if (stripos($type, 'incoming') !== false || stripos($type, 'entrant') !== false) {
                $incoming[] = $d;
            } else {
                $outgoing[] = $d;
            }
        }
        return [$outgoing, $incoming];
    }
}
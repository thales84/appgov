<?php

namespace Database\Seeders;

use App\Domain\Catalog\Enums\FormFieldType;
use App\Domain\Catalog\Enums\ProcedureRuleType;
use App\Domain\Catalog\Enums\ProcedureStepType;
use App\Domain\Catalog\Enums\ProcedureVersionStatus;
use App\Domain\Catalog\Models\ProcedureVersion;
use App\Domain\Catalog\Models\Service;
use App\Domain\Catalog\Models\ServiceCategory;
use App\Domain\Organizations\Enums\OrganizationType;
use App\Domain\Organizations\Models\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CatalogDemoSeeder extends Seeder
{
    /**
     * @var list<array<string, mixed>>
     */
    private const CATEGORIES = [
        [
            'code' => 'MOBILITY',
            'name_fr' => 'Mobilité & Transports',
            'name_en' => 'Mobility & Transport',
            'description_fr' => 'Permis de conduire et démarches liées aux déplacements.',
            'description_en' => 'Driving licences and mobility-related procedures.',
            'color_key' => 'mobility',
            'sort_order' => 10,
        ],
        [
            'code' => 'IMMIGRATION',
            'name_fr' => 'Séjour & Immigration',
            'name_en' => 'Residence & Immigration',
            'description_fr' => 'Titres de séjour et démarches d’immigration.',
            'description_en' => 'Residence permits and immigration procedures.',
            'color_key' => 'immigration',
            'sort_order' => 20,
        ],
        [
            'code' => 'NATIONALITY',
            'name_fr' => 'Nationalité & Citoyenneté',
            'name_en' => 'Nationality & Citizenship',
            'description_fr' => 'Naturalisation et démarches relatives à la nationalité.',
            'description_en' => 'Naturalisation and nationality procedures.',
            'color_key' => 'nationality',
            'sort_order' => 30,
        ],
        [
            'code' => 'EDUCATION',
            'name_fr' => 'Éducation & Diplômes',
            'name_en' => 'Education & Diplomas',
            'description_fr' => 'Diplômes, duplicatas et authentifications.',
            'description_en' => 'Diplomas, duplicates and authentication.',
            'color_key' => 'education',
            'sort_order' => 40,
        ],
        [
            'code' => 'CIVIL_STATUS',
            'name_fr' => 'État Civil',
            'name_en' => 'Civil Status',
            'description_fr' => 'Actes, certificats et documents d’état civil.',
            'description_en' => 'Civil records, certificates and documents.',
            'color_key' => 'civil',
            'sort_order' => 50,
        ],
        [
            'code' => 'ECONOMIC_ACTIVITY',
            'name_fr' => 'Activité Économique',
            'name_en' => 'Economic Activity',
            'description_fr' => 'Autorisations, licences et formalités économiques.',
            'description_en' => 'Authorisations, licences and business formalities.',
            'color_key' => 'business',
            'sort_order' => 60,
        ],
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            foreach (self::CATEGORIES as $category) {
                $model = ServiceCategory::query()->firstOrCreate(
                    ['code' => $category['code']],
                    ['public_id' => (string) Str::ulid(), ...$category, 'is_active' => true],
                );
                $model->update([...$category, 'is_active' => true]);
            }

            $organization = Organization::query()->firstOrCreate(
                ['code' => 'MINTRANSPORT'],
                [
                    'public_id' => (string) Str::ulid(),
                    'type' => OrganizationType::Ministry,
                    'name_fr' => 'Ministère de l’Intérieur — ANTS',
                    'name_en' => 'Ministry of the Interior — ANTS',
                    'is_active' => true,
                ],
            );
            $organization->update([
                'type' => OrganizationType::Ministry,
                'name_fr' => 'Ministère de l’Intérieur — ANTS',
                'name_en' => 'Ministry of the Interior — ANTS',
                'is_active' => true,
            ]);

            // Alias fallback for tests compatibility
            Organization::query()->firstOrCreate(
                ['code' => 'DEMO-TRANSPORT-AUTHORITY'],
                [
                    'public_id' => (string) Str::ulid(),
                    'type' => OrganizationType::Ministry,
                    'name_fr' => 'Ministère de l’Intérieur — ANTS',
                    'name_en' => 'Ministry of the Interior — ANTS',
                    'is_active' => true,
                ],
            );

            $mobility = ServiceCategory::query()->where('code', 'MOBILITY')->firstOrFail();
            $service = Service::query()->firstOrCreate(
                ['code' => 'DEMO-DRIVING-LICENCE'],
                [
                    'public_id' => (string) Str::ulid(),
                    'organization_id' => $organization->id,
                    'service_category_id' => $mobility->id,
                    'name_fr' => 'Permis de conduire',
                    'name_en' => 'Driving licence',
                    'description_fr' => 'Demande, instruction et délivrance du permis de conduire en République Française via l’ANTS.',
                    'description_en' => 'Application, review and issuance of driving licence in the French Republic via ANTS.',
                    'is_active' => true,
                ],
            );
            $service->update([
                'organization_id' => $organization->id,
                'service_category_id' => $mobility->id,
                'name_fr' => 'Permis de conduire',
                'name_en' => 'Driving licence',
                'description_fr' => 'Demande, instruction et délivrance du permis de conduire en République Française via l’ANTS.',
                'description_en' => 'Application, review and issuance of driving licence in the French Republic via ANTS.',
                'is_active' => true,
            ]);

            if ($service->procedureVersions()->where('version_number', 1)->exists()) {
                $version = $service->procedureVersions()->where('version_number', 1)->first();
                $version->update([
                    'title_fr' => 'Obtenir un permis de conduire',
                    'title_en' => 'Obtain a driving licence',
                    'summary_fr' => 'Procédure officielle d’inscription, d’évaluation et d’obtention du permis de conduire.',
                    'summary_en' => 'Official procedure for registration, evaluation and driving licence acquisition.',
                    'description_fr' => 'Service public de demande, de vérification des pièces, de paiement des droits et d’organisation des épreuves théoriques et pratiques.',
                    'description_en' => 'Public service for application, document verification, fee payment and examination organization.',
                    'eligibility_fr' => 'Être âgé d’au moins 18 ans et résider en France.',
                    'eligibility_en' => 'Be at least 18 years old and reside in France.',
                    'legal_basis_fr' => 'Code de la route et réglementation relative aux titres sécurisés ANTS.',
                    'legal_basis_en' => 'Highway code and ANTS secure title standards.',
                    'is_demo' => false,
                ]);

                return;
            }

            $version = $this->createVersion($service);
            $steps = $this->createSteps($version);
            $this->createFields($version, $steps);
            $this->createDocuments($version, $steps);
            $this->createRules($version);
            $this->createFees($version, $steps);
            $version->forceFill([
                'status' => ProcedureVersionStatus::Published,
                'published_at' => now()->subDay(),
            ])->saveQuietly();
        });
    }

    private function createVersion(Service $service): ProcedureVersion
    {
        return $service->procedureVersions()->create([
            'public_id' => (string) Str::ulid(),
            'version_number' => 1,
            'status' => ProcedureVersionStatus::Draft,
            'title_fr' => 'Obtenir un permis de conduire',
            'title_en' => 'Obtain a driving licence',
            'summary_fr' => 'Procédure officielle d’inscription, d’évaluation et d’obtention du permis de conduire.',
            'summary_en' => 'Official procedure for registration, evaluation and driving licence acquisition.',
            'description_fr' => 'Service public de demande, de vérification des pièces, de paiement des droits et d’organisation des épreuves théoriques et pratiques.',
            'description_en' => 'Public service for application, document verification, fee payment and examination organization.',
            'eligibility_fr' => 'Être âgé d’au moins 18 ans et résider en France.',
            'eligibility_en' => 'Be at least 18 years old and reside in France.',
            'legal_basis_fr' => 'Code de la route et réglementation relative aux titres sécurisés ANTS.',
            'legal_basis_en' => 'Highway code and ANTS secure title standards.',
            'is_demo' => false,
            'effective_from' => now()->subDay()->startOfDay(),
        ]);
    }

    /**
     * @return array<string, int>
     */
    private function createSteps(ProcedureVersion $version): array
    {
        $definitions = [
            ['PREPARATION', 'Préparation de la demande', 'Prepare application', ProcedureStepType::Form],
            ['ADMIN_REVIEW', 'Contrôle administratif', 'Administrative review', ProcedureStepType::Review],
            ['PAYMENT', 'Paiement des droits', 'Payment of fees', ProcedureStepType::Payment],
            ['EXAMINATION', 'Épreuves d’examen', 'Examinations', ProcedureStepType::Examination],
            ['DECISION', 'Décision d’attribution', 'Decision', ProcedureStepType::Decision],
            ['PRODUCTION', 'Production et remise du titre', 'Production and delivery', ProcedureStepType::Production],
        ];

        $ids = [];
        foreach ($definitions as $index => [$code, $nameFr, $nameEn, $type]) {
            $step = $version->steps()->create([
                'public_id' => (string) Str::ulid(),
                'code' => $code,
                'position' => $index + 1,
                'name_fr' => $nameFr,
                'name_en' => $nameEn,
                'description_fr' => 'Étape officielle de la démarche.',
                'description_en' => 'Official step of the procedure.',
                'step_type' => $type,
                'is_required' => true,
            ]);
            $ids[$code] = $step->id;
        }

        return $ids;
    }

    /**
     * @param  array<string, int>  $steps
     */
    private function createFields(ProcedureVersion $version, array $steps): void
    {
        $version->formFields()->create([
            'public_id' => (string) Str::ulid(),
            'procedure_step_id' => $steps['PREPARATION'],
            'code' => 'REQUESTED_CATEGORY',
            'position' => 1,
            'field_type' => FormFieldType::Text,
            'label_fr' => 'Catégorie de permis souhaitée (ex: Catégorie B)',
            'label_en' => 'Requested licence category (e.g. Category B)',
            'help_fr' => 'Veuillez préciser la catégorie sollicitée.',
            'help_en' => 'Please specify the requested category.',
            'is_required' => true,
        ]);
    }

    /**
     * @param  array<string, int>  $steps
     */
    private function createDocuments(ProcedureVersion $version, array $steps): void
    {
        $documents = [
            ['IDENTITY_DOCUMENT', 'Carte Nationale d’Identité ou Passeport', 'National Identity Card or Passport'],
            ['PROOF_OF_ADDRESS', 'Justificatif de domicile de moins de 6 mois', 'Proof of address under 6 months old'],
            ['EPHOTO_CODE', 'Code photo-signature numérique e-photo', 'Digital e-photo photo-signature code'],
        ];

        foreach ($documents as $index => [$code, $nameFr, $nameEn]) {
            $version->documentRequirements()->create([
                'public_id' => (string) Str::ulid(),
                'procedure_step_id' => $steps['PREPARATION'],
                'code' => $code,
                'position' => $index + 1,
                'name_fr' => $nameFr,
                'name_en' => $nameEn,
                'description_fr' => 'Pièce justificative officielle obligatoire.',
                'description_en' => 'Mandatory official supporting document.',
                'is_required' => true,
            ]);
        }
    }

    private function createRules(ProcedureVersion $version): void
    {
        $version->rules()->create([
            'public_id' => (string) Str::ulid(),
            'code' => 'AGE_ELIGIBILITY',
            'position' => 1,
            'rule_type' => ProcedureRuleType::Eligibility,
            'name_fr' => 'Condition d’âge légal (18 ans)',
            'name_en' => 'Legal age requirement (18 years)',
            'description_fr' => 'Le candidat doit être âgé d’au moins 18 ans révolus au jour de l’épreuve.',
            'description_en' => 'The applicant must be at least 18 years old on the exam date.',
        ]);
    }

    /**
     * @param  array<string, int>  $steps
     */
    private function createFees(ProcedureVersion $version, array $steps): void
    {
        $version->feeSchedules()->create([
            'public_id' => (string) Str::ulid(),
            'procedure_step_id' => $steps['PAYMENT'],
            'code' => 'LICENCE_FEE',
            'position' => 1,
            'label_fr' => 'Droits de délivrance du permis et timbre fiscal',
            'label_en' => 'Licence issuance fee and tax stamp',
            'description_fr' => 'Droits d’enregistrement et confection du permis de conduire.',
            'description_en' => 'Registration and driving licence manufacturing fee.',
            'amount_minor' => 3000,
            'currency' => 'EUR',
            'minor_unit_exponent' => 2,
            'is_mandatory' => true,
            'due_when_fr' => 'Après validation du contrôle administratif',
            'due_when_en' => 'After administrative review validation',
            'legal_basis_fr' => 'Code général des impôts — Timbre fiscal permis de conduire',
            'legal_basis_en' => 'General Tax Code — Driving licence tax stamp',
        ]);
    }
}

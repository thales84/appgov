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
            'name_fr' => 'Mobilité',
            'name_en' => 'Mobility',
            'description_fr' => 'Permis de conduire et démarches liées aux déplacements.',
            'description_en' => 'Driving licences and mobility-related procedures.',
            'color_key' => 'mobility',
            'sort_order' => 10,
        ],
        [
            'code' => 'IMMIGRATION',
            'name_fr' => 'Séjour et immigration',
            'name_en' => 'Residence and immigration',
            'description_fr' => 'Titres de séjour et démarches d’immigration.',
            'description_en' => 'Residence permits and immigration procedures.',
            'color_key' => 'immigration',
            'sort_order' => 20,
        ],
        [
            'code' => 'NATIONALITY',
            'name_fr' => 'Nationalité',
            'name_en' => 'Nationality',
            'description_fr' => 'Naturalisation et démarches relatives à la nationalité.',
            'description_en' => 'Naturalisation and nationality procedures.',
            'color_key' => 'nationality',
            'sort_order' => 30,
        ],
        [
            'code' => 'EDUCATION',
            'name_fr' => 'Éducation',
            'name_en' => 'Education',
            'description_fr' => 'Diplômes, duplicatas et authentifications.',
            'description_en' => 'Diplomas, duplicates and authentication.',
            'color_key' => 'education',
            'sort_order' => 40,
        ],
        [
            'code' => 'CIVIL_STATUS',
            'name_fr' => 'État civil',
            'name_en' => 'Civil status',
            'description_fr' => 'Actes, certificats et documents d’état civil.',
            'description_en' => 'Civil records, certificates and documents.',
            'color_key' => 'civil',
            'sort_order' => 50,
        ],
        [
            'code' => 'ECONOMIC_ACTIVITY',
            'name_fr' => 'Activité économique',
            'name_en' => 'Economic activity',
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
                ['code' => 'DEMO-TRANSPORT-AUTHORITY'],
                [
                    'public_id' => (string) Str::ulid(),
                    'type' => OrganizationType::PublicAgency,
                    'name_fr' => 'Administration de démonstration — non officielle',
                    'name_en' => 'Demonstration administration — unofficial',
                    'is_active' => true,
                ],
            );
            $organization->update([
                'type' => OrganizationType::PublicAgency,
                'name_fr' => 'Administration de démonstration — non officielle',
                'name_en' => 'Demonstration administration — unofficial',
                'is_active' => true,
            ]);

            $mobility = ServiceCategory::query()->where('code', 'MOBILITY')->firstOrFail();
            $service = Service::query()->firstOrCreate(
                ['code' => 'DEMO-DRIVING-LICENCE'],
                [
                    'public_id' => (string) Str::ulid(),
                    'organization_id' => $organization->id,
                    'service_category_id' => $mobility->id,
                    'name_fr' => 'Permis de conduire — parcours DÉMO',
                    'name_en' => 'Driving licence — DEMO journey',
                    'description_fr' => 'Exemple de démarche numérique destiné à valider la plateforme. Aucune règle affichée n’est officielle.',
                    'description_en' => 'Example digital procedure used to validate the platform. No displayed rule is official.',
                    'is_active' => true,
                ],
            );
            $service->update([
                'organization_id' => $organization->id,
                'service_category_id' => $mobility->id,
                'name_fr' => 'Permis de conduire — parcours DÉMO',
                'name_en' => 'Driving licence — DEMO journey',
                'description_fr' => 'Exemple de démarche numérique destiné à valider la plateforme. Aucune règle affichée n’est officielle.',
                'description_en' => 'Example digital procedure used to validate the platform. No displayed rule is official.',
                'is_active' => true,
            ]);

            if ($service->procedureVersions()->where('version_number', 1)->exists()) {
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
            'title_fr' => 'DÉMO — Obtenir un permis de conduire',
            'title_en' => 'DEMO — Obtain a driving licence',
            'summary_fr' => 'Parcours entièrement fictif pour tester l’information, les étapes et le démarrage d’un dossier.',
            'summary_en' => 'Entirely fictional journey used to test information, steps and application start.',
            'description_fr' => 'Cette version illustre le fonctionnement d’AppGov. Les catégories, pièces, règles, délais et tarifs doivent être remplacés par des contenus validés avant toute utilisation administrative.',
            'description_en' => 'This version illustrates how AppGov works. Categories, documents, rules, timelines and fees must be replaced with approved content before any administrative use.',
            'eligibility_fr' => 'DÉMO : aucun critère officiel n’est configuré. L’autorité compétente doit définir et valider les conditions applicables.',
            'eligibility_en' => 'DEMO: no official eligibility criterion is configured. The competent authority must define and approve the applicable conditions.',
            'legal_basis_fr' => 'Aucune base réglementaire officielle — contenu DÉMO.',
            'legal_basis_en' => 'No official legal basis — DEMO content.',
            'is_demo' => true,
            'effective_from' => now()->subDay()->startOfDay(),
        ]);
    }

    /**
     * @return array<string, int>
     */
    private function createSteps(ProcedureVersion $version): array
    {
        $definitions = [
            ['PREPARATION', 'Préparer la demande — DÉMO', 'Prepare the application — DEMO', ProcedureStepType::Form],
            ['ADMIN_REVIEW', 'Contrôle administratif — DÉMO', 'Administrative review — DEMO', ProcedureStepType::Review],
            ['PAYMENT', 'Paiement éventuel — DÉMO', 'Possible payment — DEMO', ProcedureStepType::Payment],
            ['EXAMINATION', 'Épreuves — DÉMO', 'Examinations — DEMO', ProcedureStepType::Examination],
            ['DECISION', 'Décision — DÉMO', 'Decision — DEMO', ProcedureStepType::Decision],
            ['PRODUCTION', 'Production et disponibilité — DÉMO', 'Production and availability — DEMO', ProcedureStepType::Production],
        ];

        $ids = [];
        foreach ($definitions as $index => [$code, $nameFr, $nameEn, $type]) {
            $step = $version->steps()->create([
                'public_id' => (string) Str::ulid(),
                'code' => $code,
                'position' => $index + 1,
                'name_fr' => $nameFr,
                'name_en' => $nameEn,
                'description_fr' => 'Étape indicative sans valeur administrative.',
                'description_en' => 'Illustrative step with no administrative value.',
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
            'label_fr' => 'Catégorie de permis souhaitée — DÉMO',
            'label_en' => 'Requested licence category — DEMO',
            'help_fr' => 'La liste officielle des catégories reste à valider.',
            'help_en' => 'The official category list still requires approval.',
            'is_required' => true,
        ]);
    }

    /**
     * @param  array<string, int>  $steps
     */
    private function createDocuments(ProcedureVersion $version, array $steps): void
    {
        $documents = [
            ['IDENTITY_EXAMPLE', 'Pièce d’identité — exemple DÉMO', 'Identity document — DEMO example'],
            ['PHOTO_EXAMPLE', 'Photo d’identité — exemple DÉMO', 'Identity photograph — DEMO example'],
        ];

        foreach ($documents as $index => [$code, $nameFr, $nameEn]) {
            $version->documentRequirements()->create([
                'public_id' => (string) Str::ulid(),
                'procedure_step_id' => $steps['PREPARATION'],
                'code' => $code,
                'position' => $index + 1,
                'name_fr' => $nameFr,
                'name_en' => $nameEn,
                'description_fr' => 'Pièce fictive à remplacer par une exigence officiellement validée.',
                'description_en' => 'Fictional document to be replaced by an officially approved requirement.',
                'is_required' => true,
            ]);
        }
    }

    private function createRules(ProcedureVersion $version): void
    {
        $version->rules()->create([
            'public_id' => (string) Str::ulid(),
            'code' => 'DEMO_ELIGIBILITY',
            'position' => 1,
            'rule_type' => ProcedureRuleType::Eligibility,
            'name_fr' => 'Éligibilité à confirmer — DÉMO',
            'name_en' => 'Eligibility to be confirmed — DEMO',
            'description_fr' => 'Aucun âge, catégorie ou critère réglementaire n’est supposé par cette démonstration.',
            'description_en' => 'This demonstration assumes no age, category or regulatory criterion.',
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
            'code' => 'DEMO_FEE',
            'position' => 1,
            'label_fr' => 'Tarif de démonstration — montant non officiel',
            'label_en' => 'Demonstration fee — unofficial amount',
            'description_fr' => 'Le montant est volontairement fixé à zéro tant que le tarif officiel n’est pas validé.',
            'description_en' => 'The amount is deliberately set to zero until an official fee is approved.',
            'amount_minor' => 0,
            'currency' => 'XAF',
            'minor_unit_exponent' => 0,
            'is_mandatory' => false,
            'due_when_fr' => 'À confirmer par l’autorité compétente',
            'due_when_en' => 'To be confirmed by the competent authority',
            'legal_basis_fr' => null,
            'legal_basis_en' => null,
        ]);
    }
}

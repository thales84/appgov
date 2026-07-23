<?php

use App\Domain\Catalog\Enums\ProcedureVersionStatus;
use App\Domain\Catalog\Models\ProcedureVersion;
use App\Domain\Catalog\Models\Service;
use App\Domain\Catalog\Models\ServiceCategory;
use App\Domain\Organizations\Enums\OrganizationType;
use App\Domain\Organizations\Models\AgentAssignment;
use App\Domain\Organizations\Models\Organization;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(AccessControlSeeder::class);
});

function createCatalogOrganization(string $suffix): Organization
{
    return Organization::create([
        'code' => "DEMO-ORG-$suffix",
        'type' => OrganizationType::PublicAgency,
        'name_fr' => "Organisme DEMO $suffix",
        'name_en' => "DEMO organisation $suffix",
        'is_active' => true,
    ]);
}

function createCatalogAgent(string $role, Organization $organization): User
{
    $agent = User::factory()->agent()->create();
    $agent->assignRole($role);
    $agent->forceFill([
        'two_factor_secret' => 'configured',
        'two_factor_recovery_codes' => 'configured',
        'two_factor_confirmed_at' => now(),
    ])->save();

    AgentAssignment::create([
        'user_id' => $agent->id,
        'organization_id' => $organization->id,
        'starts_at' => now()->subDay(),
        'is_active' => true,
    ]);

    return $agent;
}

function createCatalogCategory(): ServiceCategory
{
    return ServiceCategory::create([
        'code' => 'DEMO-MOBILITY-'.Str::upper(Str::random(6)),
        'name_fr' => 'Mobilité DEMO',
        'name_en' => 'DEMO mobility',
        'description_fr' => 'Catégorie de démonstration.',
        'description_en' => 'Demonstration category.',
        'color_key' => 'mobility',
        'sort_order' => 10,
        'is_active' => true,
    ]);
}

function completeVersionPayload(): array
{
    return [
        'title_fr' => 'DÉMO — Demande de permis',
        'title_en' => 'DEMO — Driving licence application',
        'summary_fr' => 'Parcours de démonstration sans valeur administrative.',
        'summary_en' => 'Demonstration journey with no administrative value.',
        'description_fr' => 'Cette définition sert uniquement à tester le catalogue.',
        'description_en' => 'This definition is only used to test the catalogue.',
        'eligibility_fr' => 'Critères à valider par l’autorité compétente.',
        'eligibility_en' => 'Criteria to be approved by the competent authority.',
        'legal_basis_fr' => null,
        'legal_basis_en' => null,
        'effective_from' => now()->addDay()->toDateString(),
        'is_demo' => true,
        'steps' => [
            [
                'code' => 'PREPARATION',
                'name_fr' => 'Préparer la demande',
                'name_en' => 'Prepare the application',
                'description_fr' => 'Étape de démonstration.',
                'description_en' => 'Demonstration step.',
                'step_type' => 'form',
                'is_required' => true,
            ],
        ],
        'fields' => [
            [
                'step_code' => 'PREPARATION',
                'code' => 'REQUESTED_CATEGORY',
                'label_fr' => 'Catégorie souhaitée — DEMO',
                'label_en' => 'Requested category — DEMO',
                'help_fr' => 'Liste à valider.',
                'help_en' => 'List to be approved.',
                'field_type' => 'text',
                'is_required' => true,
            ],
        ],
        'documents' => [
            [
                'step_code' => 'PREPARATION',
                'code' => 'IDENTITY_EXAMPLE',
                'name_fr' => 'Pièce d’identité — exemple non officiel',
                'name_en' => 'Identity document — unofficial example',
                'description_fr' => 'Exigence de démonstration.',
                'description_en' => 'Demonstration requirement.',
                'is_required' => true,
            ],
        ],
        'rules' => [
            [
                'code' => 'DEMO_ELIGIBILITY',
                'name_fr' => 'Règle d’éligibilité DEMO',
                'name_en' => 'DEMO eligibility rule',
                'description_fr' => 'Règle non officielle à faire valider.',
                'description_en' => 'Unofficial rule requiring approval.',
                'rule_type' => 'eligibility',
            ],
        ],
        'fees' => [
            [
                'step_code' => null,
                'code' => 'DEMO_FEE',
                'label_fr' => 'Tarif DEMO — montant non officiel',
                'label_en' => 'DEMO fee — unofficial amount',
                'description_fr' => 'Aucun montant officiel n’est configuré.',
                'description_en' => 'No official amount is configured.',
                'amount_minor' => 0,
                'currency' => 'XAF',
                'minor_unit_exponent' => 0,
                'is_mandatory' => false,
                'due_when_fr' => 'À confirmer',
                'due_when_en' => 'To be confirmed',
                'legal_basis_fr' => null,
                'legal_basis_en' => null,
            ],
        ],
    ];
}

it('creates and edits a complete draft inside the agent organization', function () {
    $organization = createCatalogOrganization('A');
    $editor = createCatalogAgent('catalog_administrator', $organization);
    $category = createCatalogCategory();

    $response = $this->actingAs($editor)->post(route('admin.catalog.services.store'), [
        'category_public_id' => $category->public_id,
        'code' => 'DEMO-DRIVING-LICENCE',
        'name_fr' => 'Permis de conduire — DEMO',
        'name_en' => 'Driving licence — DEMO',
        'description_fr' => 'Service de démonstration.',
        'description_en' => 'Demonstration service.',
    ]);

    $service = Service::where('code', 'DEMO-DRIVING-LICENCE')->firstOrFail();
    $version = $service->procedureVersions()->firstOrFail();

    $response->assertRedirect(route('admin.catalog.versions.show', $version));

    $this->actingAs($editor)
        ->put(route('admin.catalog.versions.update', $version), completeVersionPayload())
        ->assertRedirect(route('admin.catalog.versions.show', $version));

    expect($version->fresh())
        ->title_fr->toBe('DÉMO — Demande de permis')
        ->status->toBe(ProcedureVersionStatus::Draft)
        ->is_demo->toBeTrue()
        ->and($version->steps()->count())->toBe(1)
        ->and($version->formFields()->count())->toBe(1)
        ->and($version->documentRequirements()->count())->toBe(1)
        ->and($version->rules()->count())->toBe(1)
        ->and($version->feeSchedules()->count())->toBe(1);
});

it('allows only a category manager to extend the category catalogue', function () {
    $organization = createCatalogOrganization('CATEGORY');
    $editor = createCatalogAgent('catalog_administrator', $organization);
    $administrator = createCatalogAgent('platform_administrator', $organization);
    $payload = [
        'code' => 'DEMO-HEALTH',
        'name_fr' => 'Santé DEMO',
        'name_en' => 'DEMO health',
        'description_fr' => 'Catégorie non officielle.',
        'description_en' => 'Unofficial category.',
        'color_key' => 'civil',
        'sort_order' => 70,
    ];

    $this->actingAs($editor)
        ->post(route('admin.catalog.categories.store'), $payload)
        ->assertForbidden();

    $this->actingAs($administrator)
        ->post(route('admin.catalog.categories.store'), $payload)
        ->assertRedirect(route('admin.catalog.index'));

    $this->assertDatabaseHas('service_categories', ['code' => 'DEMO-HEALTH']);
    $this->assertDatabaseHas('activity_log', ['event' => 'catalog.category.created']);
});

it('separates editing from publication and makes the published aggregate immutable', function () {
    $organization = createCatalogOrganization('B');
    $editor = createCatalogAgent('catalog_administrator', $organization);
    $publisher = createCatalogAgent('platform_administrator', $organization);
    $category = createCatalogCategory();

    $this->actingAs($editor)->post(route('admin.catalog.services.store'), [
        'category_public_id' => $category->public_id,
        'code' => 'DEMO-PUBLISHABLE',
        'name_fr' => 'Service publiable DEMO',
        'name_en' => 'DEMO publishable service',
        'description_fr' => 'Démonstration.',
        'description_en' => 'Demonstration.',
    ]);

    $version = Service::where('code', 'DEMO-PUBLISHABLE')
        ->firstOrFail()
        ->procedureVersions()
        ->firstOrFail();

    $this->actingAs($editor)
        ->put(route('admin.catalog.versions.update', $version), completeVersionPayload())
        ->assertRedirect();

    $this->actingAs($editor)
        ->post(route('admin.catalog.versions.submit-review', $version))
        ->assertRedirect();

    $this->actingAs($editor)
        ->post(route('admin.catalog.versions.publish', $version))
        ->assertForbidden();

    $this->actingAs($publisher)
        ->post(route('admin.catalog.versions.publish', $version))
        ->assertRedirect();

    $published = $version->fresh();
    $step = $published->steps()->firstOrFail();

    expect($published->status)->toBe(ProcedureVersionStatus::Published)
        ->and($published->published_by_user_id)->toBe($publisher->id);

    expect(fn () => $published->update(['title_fr' => 'Modification interdite']))
        ->toThrow(LogicException::class);

    expect(fn () => $step->update(['name_fr' => 'Modification interdite']))
        ->toThrow(LogicException::class);

    expect(fn () => $published->steps()->create([
        'code' => 'ILLEGAL',
        'position' => 2,
        'name_fr' => 'Interdit',
        'name_en' => 'Forbidden',
        'step_type' => 'form',
        'is_required' => true,
    ]))->toThrow(LogicException::class);

    $this->assertDatabaseHas('activity_log', [
        'event' => 'catalog.procedure.published',
        'subject_id' => $published->id,
    ]);
});

it('scopes the catalog administration to active organization assignments', function () {
    $organizationA = createCatalogOrganization('C-A');
    $organizationB = createCatalogOrganization('C-B');
    $agentA = createCatalogAgent('catalog_administrator', $organizationA);
    $agentB = createCatalogAgent('catalog_administrator', $organizationB);
    $category = createCatalogCategory();

    foreach ([[$organizationA, $agentA, 'A'], [$organizationB, $agentB, 'B']] as [$organization, $agent, $suffix]) {
        $this->actingAs($agent)->post(route('admin.catalog.services.store'), [
            'category_public_id' => $category->public_id,
            'code' => "DEMO-SERVICE-$suffix",
            'name_fr' => "Service DEMO $suffix",
            'name_en' => "DEMO service $suffix",
            'description_fr' => 'Démonstration.',
            'description_en' => 'Demonstration.',
        ])->assertRedirect();
    }

    $foreignVersion = Service::where('organization_id', $organizationB->id)
        ->firstOrFail()
        ->procedureVersions()
        ->firstOrFail();

    $this->actingAs($agentA)
        ->get(route('admin.catalog.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Catalog/Index')
            ->has('services.data', 1)
            ->where('services.data.0.code', 'DEMO-SERVICE-A')
        );

    $this->actingAs($agentA)
        ->get(route('admin.catalog.versions.show', $foreignVersion))
        ->assertForbidden();
});

it('requires an independent reviewer to publish a version', function () {
    $organization = createCatalogOrganization('D');
    $administrator = createCatalogAgent('platform_administrator', $organization);
    $category = createCatalogCategory();

    $this->actingAs($administrator)->post(route('admin.catalog.services.store'), [
        'category_public_id' => $category->public_id,
        'code' => 'DEMO-OWN-VERSION',
        'name_fr' => 'Service personnel DEMO',
        'name_en' => 'DEMO own service',
        'description_fr' => 'Démonstration.',
        'description_en' => 'Demonstration.',
    ])->assertRedirect();

    $version = ProcedureVersion::firstOrFail();
    $this->actingAs($administrator)
        ->put(route('admin.catalog.versions.update', $version), completeVersionPayload())
        ->assertRedirect();
    $this->actingAs($administrator)
        ->post(route('admin.catalog.versions.submit-review', $version))
        ->assertRedirect();
    $this->actingAs($administrator)
        ->post(route('admin.catalog.versions.publish', $version))
        ->assertForbidden();
});

it('returns a reviewed version to draft with an audited reason', function () {
    $organization = createCatalogOrganization('E');
    $editor = createCatalogAgent('catalog_administrator', $organization);
    $reviewer = createCatalogAgent('platform_administrator', $organization);
    $category = createCatalogCategory();

    $this->actingAs($editor)->post(route('admin.catalog.services.store'), [
        'category_public_id' => $category->public_id,
        'code' => 'DEMO-RETURN',
        'name_fr' => 'Service à corriger DEMO',
        'name_en' => 'DEMO service to revise',
        'description_fr' => 'Démonstration.',
        'description_en' => 'Demonstration.',
    ])->assertRedirect();

    $version = ProcedureVersion::firstOrFail();
    $this->actingAs($editor)->put(
        route('admin.catalog.versions.update', $version),
        completeVersionPayload()
    )->assertRedirect();
    $this->actingAs($editor)
        ->post(route('admin.catalog.versions.submit-review', $version))
        ->assertRedirect();

    $this->actingAs($reviewer)
        ->post(route('admin.catalog.versions.return-draft', $version), [
            'reason' => 'Préciser le caractère non officiel des pièces demandées.',
        ])
        ->assertRedirect();

    expect($version->fresh()->status)->toBe(ProcedureVersionStatus::Draft);
    $this->assertDatabaseHas('activity_log', [
        'event' => 'catalog.procedure.returned_to_draft',
        'subject_id' => $version->id,
    ]);
});

it('retires a published version and clones its definition into a new draft', function () {
    $organization = createCatalogOrganization('F');
    $editor = createCatalogAgent('catalog_administrator', $organization);
    $publisher = createCatalogAgent('platform_administrator', $organization);
    $category = createCatalogCategory();

    $this->actingAs($editor)->post(route('admin.catalog.services.store'), [
        'category_public_id' => $category->public_id,
        'code' => 'DEMO-RETIRED',
        'name_fr' => 'Service à retirer DEMO',
        'name_en' => 'DEMO service to retire',
        'description_fr' => 'Démonstration.',
        'description_en' => 'Demonstration.',
    ])->assertRedirect();

    $version = ProcedureVersion::firstOrFail();
    $this->actingAs($editor)
        ->put(route('admin.catalog.versions.update', $version), completeVersionPayload())
        ->assertRedirect();
    $this->actingAs($editor)
        ->post(route('admin.catalog.versions.submit-review', $version))
        ->assertRedirect();
    $this->actingAs($publisher)
        ->post(route('admin.catalog.versions.publish', $version))
        ->assertRedirect();

    $this->actingAs($editor)
        ->post(route('admin.catalog.versions.retire', $version), [
            'reason' => 'Le service de démonstration doit être remplacé.',
        ])
        ->assertForbidden();

    $this->actingAs($publisher)
        ->post(route('admin.catalog.versions.retire', $version), [
            'reason' => 'Le service de démonstration doit être remplacé.',
        ])
        ->assertRedirect();

    $this->actingAs($editor)
        ->post(route('admin.catalog.services.versions.store', $version->service))
        ->assertRedirect();

    $newVersion = $version->service->procedureVersions()->where('version_number', 2)->firstOrFail();

    expect($version->fresh()->status)->toBe(ProcedureVersionStatus::Retired)
        ->and($newVersion->status)->toBe(ProcedureVersionStatus::Draft)
        ->and($newVersion->effective_from)->toBeNull()
        ->and($newVersion->steps()->count())->toBe($version->steps()->count())
        ->and($newVersion->feeSchedules()->count())->toBe($version->feeSchedules()->count());

    $this->assertDatabaseHas('activity_log', [
        'event' => 'catalog.procedure.retired',
        'subject_id' => $version->id,
    ]);
    $this->assertDatabaseHas('activity_log', [
        'event' => 'catalog.procedure.version_created',
        'subject_id' => $newVersion->id,
    ]);
});

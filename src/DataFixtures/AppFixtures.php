<?php

namespace App\DataFixtures;

use App\Entity\Document;
use App\Entity\Organization;
use App\Entity\Template;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        // ========== ORGANISATIONS ==========
        $org1 = new Organization();
        $org1->setName('LegalDocs Corp');
        $org1->setSettings(['plan' => 'enterprise']);
        $org1->setCreatedAt(new \DateTimeImmutable('-6 months'));
        $manager->persist($org1);

        $org2 = new Organization();
        $org2->setName('Cabinet Martin');
        $org2->setSettings(['plan' => 'pro']);
        $org2->setCreatedAt(new \DateTimeImmutable('-3 months'));
        $manager->persist($org2);

        $org3 = new Organization();
        $org3->setName('Startup Innov');
        $org3->setSettings(['plan' => 'free']);
        $org3->setCreatedAt(new \DateTimeImmutable('-1 month'));
        $manager->persist($org3);

        // ========== UTILISATEURS ==========

        // Super Admin - Louis (compte personnel, pas affiché sur login)
        $louisAdmin = new User();
        $louisAdmin->setEmail('l.zerri@gmail.com');
        $louisAdmin->setName('Louis Zerri');
        $louisAdmin->setRoles(['ROLE_SUPER_ADMIN']);
        $louisAdmin->setPassword($this->passwordHasher->hashPassword($louisAdmin, 'jeux video'));
        $louisAdmin->setCreatedAt(new \DateTimeImmutable('-6 months'));
        $manager->persist($louisAdmin);

        // Éditeur (LegalDocs Corp) - Compte démo
        $editor = new User();
        $editor->setEmail('editeur@legaldocs.fr');
        $editor->setName('Marie Dupont');
        $editor->setRoles(['ROLE_EDITOR']);
        $editor->setOrganization($org1);
        $editor->setPassword($this->passwordHasher->hashPassword($editor, 'password'));
        $editor->setCreatedAt(new \DateTimeImmutable('-5 months'));
        $manager->persist($editor);

        // Validateur (LegalDocs Corp) - Compte démo
        $validator = new User();
        $validator->setEmail('juriste@legaldocs.fr');
        $validator->setName('Pierre Martin');
        $validator->setRoles(['ROLE_VALIDATOR']);
        $validator->setOrganization($org1);
        $validator->setPassword($this->passwordHasher->hashPassword($validator, 'password'));
        $validator->setCreatedAt(new \DateTimeImmutable('-4 months'));
        $manager->persist($validator);

        // User simple (Cabinet Martin) - Compte démo
        $user = new User();
        $user->setEmail('user@legaldocs.fr');
        $user->setName('Sophie Laurent');
        $user->setRoles(['ROLE_USER']);
        $user->setOrganization($org2);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
        $user->setCreatedAt(new \DateTimeImmutable('-3 months'));
        $manager->persist($user);

        // User Startup - Compte démo
        $userStartup = new User();
        $userStartup->setEmail('startup@legaldocs.fr');
        $userStartup->setName('Lucas Bernard');
        $userStartup->setRoles(['ROLE_EDITOR']);
        $userStartup->setOrganization($org3);
        $userStartup->setPassword($this->passwordHasher->hashPassword($userStartup, 'password'));
        $userStartup->setCreatedAt(new \DateTimeImmutable('-1 month'));
        $manager->persist($userStartup);

        // ========== TEMPLATES ==========

        // Template NDA (global)
        $templateNda = new Template();
        $templateNda->setName('Accord de Confidentialité (NDA)');
        $templateNda->setDescription('Accord de confidentialité bilatéral pour protéger les informations sensibles.');
        $templateNda->setVisibility('global');
        $templateNda->setCreatedBy($louisAdmin);
        $templateNda->setCreatedAt(new \DateTimeImmutable('-6 months'));
        $templateNda->setBodyMarkdown(<<<'MARKDOWN'
# Accord de Confidentialité

Entre les soussignés :

**{{partie_1_nom}}**, {{partie_1_forme_juridique}}, dont le siège social est situé {{partie_1_adresse}}, représentée par {{partie_1_representant}},

Ci-après dénommée « **La Partie Divulgatrice** »

Et

**{{partie_2_nom}}**, {{partie_2_forme_juridique}}, dont le siège social est situé {{partie_2_adresse}}, représentée par {{partie_2_representant}},

Ci-après dénommée « **La Partie Réceptrice** »

## Article 1 - Objet

Le présent accord a pour objet de définir les conditions dans lesquelles la Partie Réceptrice s'engage à maintenir confidentielles les informations qui lui seront communiquées par la Partie Divulgatrice dans le cadre de {{objet_collaboration:textarea}}.

## Article 2 - Durée de confidentialité

Les obligations de confidentialité prévues au présent accord resteront en vigueur pendant une durée de **{{duree_confidentialite:number}} ans** à compter de la date de signature.

## Article 3 - Obligations

La Partie Réceptrice s'engage à :
- Ne pas divulguer les Informations Confidentielles à des tiers
- Protéger les Informations Confidentielles avec le même degré de précaution que ses propres informations confidentielles

Fait à {{lieu_signature}}, le {{date_signature:date}}
MARKDOWN);
        $templateNda->setVariablesJson([
            ['name' => 'partie_1_nom', 'type' => 'text', 'required' => true, 'label' => 'Nom partie 1'],
            ['name' => 'partie_1_forme_juridique', 'type' => 'text', 'required' => true, 'label' => 'Forme juridique partie 1'],
            ['name' => 'partie_1_adresse', 'type' => 'textarea', 'required' => true, 'label' => 'Adresse partie 1'],
            ['name' => 'partie_1_representant', 'type' => 'text', 'required' => true, 'label' => 'Représentant partie 1'],
            ['name' => 'partie_2_nom', 'type' => 'text', 'required' => true, 'label' => 'Nom partie 2'],
            ['name' => 'partie_2_forme_juridique', 'type' => 'text', 'required' => true, 'label' => 'Forme juridique partie 2'],
            ['name' => 'partie_2_adresse', 'type' => 'textarea', 'required' => true, 'label' => 'Adresse partie 2'],
            ['name' => 'partie_2_representant', 'type' => 'text', 'required' => true, 'label' => 'Représentant partie 2'],
            ['name' => 'objet_collaboration', 'type' => 'textarea', 'required' => true, 'label' => 'Objet de la collaboration'],
            ['name' => 'duree_confidentialite', 'type' => 'number', 'required' => true, 'label' => 'Durée de confidentialité (années)'],
            ['name' => 'lieu_signature', 'type' => 'text', 'required' => true, 'label' => 'Lieu de signature'],
            ['name' => 'date_signature', 'type' => 'date', 'required' => true, 'label' => 'Date de signature'],
        ]);
        $manager->persist($templateNda);

        // Template CGV (global)
        $templateCgv = new Template();
        $templateCgv->setName('Conditions Générales de Vente');
        $templateCgv->setDescription('CGV standard pour prestations de services.');
        $templateCgv->setVisibility('global');
        $templateCgv->setCreatedBy($louisAdmin);
        $templateCgv->setCreatedAt(new \DateTimeImmutable('-5 months'));
        $templateCgv->setBodyMarkdown(<<<'MARKDOWN'
# Conditions Générales de Vente

## Article 1 - Identification du prestataire

**{{entreprise_nom}}** {{entreprise_forme_juridique}} au capital de {{entreprise_capital:number}} €
Siège social : {{entreprise_adresse:textarea}}
SIRET : {{entreprise_siret}}

## Article 2 - Objet

Les présentes CGV régissent les relations contractuelles entre {{entreprise_nom}} et ses clients pour toute prestation de {{type_prestation}}.

## Article 3 - Prix

Les prix sont exprimés en euros et s'entendent {{prix_ht_ttc:select:HT,TTC}}.
Tarif horaire : {{tarif_horaire:number}} €

## Article 4 - Paiement

Le paiement est dû à {{delai_paiement:number}} jours à compter de la date de facturation.
Mode de paiement accepté : {{mode_paiement:select:Virement,Chèque,Carte bancaire,Espèces}}

Fait le {{date_redaction:date}}
MARKDOWN);
        $templateCgv->setVariablesJson([
            ['name' => 'entreprise_nom', 'type' => 'text', 'required' => true, 'label' => 'Nom de l\'entreprise'],
            ['name' => 'entreprise_forme_juridique', 'type' => 'text', 'required' => true, 'label' => 'Forme juridique'],
            ['name' => 'entreprise_capital', 'type' => 'number', 'required' => true, 'label' => 'Capital social (€)'],
            ['name' => 'entreprise_adresse', 'type' => 'textarea', 'required' => true, 'label' => 'Adresse du siège'],
            ['name' => 'entreprise_siret', 'type' => 'text', 'required' => true, 'label' => 'Numéro SIRET'],
            ['name' => 'type_prestation', 'type' => 'text', 'required' => true, 'label' => 'Type de prestation'],
            ['name' => 'prix_ht_ttc', 'type' => 'select', 'required' => true, 'label' => 'Prix HT ou TTC', 'options' => ['HT', 'TTC']],
            ['name' => 'tarif_horaire', 'type' => 'number', 'required' => true, 'label' => 'Tarif horaire (€)'],
            ['name' => 'delai_paiement', 'type' => 'number', 'required' => true, 'label' => 'Délai de paiement (jours)'],
            ['name' => 'mode_paiement', 'type' => 'select', 'required' => true, 'label' => 'Mode de paiement', 'options' => ['Virement', 'Chèque', 'Carte bancaire', 'Espèces']],
            ['name' => 'date_redaction', 'type' => 'date', 'required' => true, 'label' => 'Date de rédaction'],
        ]);
        $manager->persist($templateCgv);

        // Template Contrat SaaS (privé - LegalDocs Corp)
        $templateSaas = new Template();
        $templateSaas->setName('Contrat SaaS');
        $templateSaas->setDescription('Contrat de licence logicielle en mode SaaS.');
        $templateSaas->setVisibility('private');
        $templateSaas->setOrganization($org1);
        $templateSaas->setCreatedBy($editor);
        $templateSaas->setCreatedAt(new \DateTimeImmutable('-4 months'));
        $templateSaas->setBodyMarkdown(<<<'MARKDOWN'
# Contrat de Licence SaaS

## Article 1 - Objet

Le présent contrat a pour objet de définir les conditions d'utilisation de la solution SaaS fournie par LegalDocs Corp au client **{{client_nom}}**.

## Article 2 - Durée

Le contrat est conclu pour une durée de **{{duree_contrat:number}} mois** à compter du {{date_debut:date}}.

## Article 3 - Tarification

L'abonnement mensuel est fixé à **{{montant_mensuel:number}} €** HT.
Mode de paiement : {{mode_paiement:select:Virement,Prélèvement automatique,Carte bancaire}}

## Article 4 - Support

Le client bénéficie d'un support technique par email et téléphone, du lundi au vendredi de 9h à 18h.
MARKDOWN);
        $templateSaas->setVariablesJson([
            ['name' => 'client_nom', 'type' => 'text', 'required' => true, 'label' => 'Nom du client'],
            ['name' => 'duree_contrat', 'type' => 'number', 'required' => true, 'label' => 'Durée du contrat (mois)'],
            ['name' => 'date_debut', 'type' => 'date', 'required' => true, 'label' => 'Date de début'],
            ['name' => 'montant_mensuel', 'type' => 'number', 'required' => true, 'label' => 'Montant mensuel (€)'],
            ['name' => 'mode_paiement', 'type' => 'select', 'required' => true, 'label' => 'Mode de paiement', 'options' => ['Virement', 'Prélèvement automatique', 'Carte bancaire']],
        ]);
        $manager->persist($templateSaas);

        // Template Contrat de travail (privé - Cabinet Martin)
        $templateTravail = new Template();
        $templateTravail->setName('Contrat de Travail CDI');
        $templateTravail->setDescription('Contrat de travail à durée indéterminée.');
        $templateTravail->setVisibility('private');
        $templateTravail->setOrganization($org2);
        $templateTravail->setCreatedBy($user);
        $templateTravail->setCreatedAt(new \DateTimeImmutable('-2 months'));
        $templateTravail->setBodyMarkdown(<<<'MARKDOWN'
# Contrat de Travail à Durée Indéterminée

Entre l'employeur **{{employeur_nom}}** et le salarié **{{salarie_nom}}**.

## Article 1 - Engagement

Le salarié est engagé en qualité de **{{poste}}** à compter du {{date_debut:date}}.

## Article 2 - Rémunération

Le salaire brut mensuel est fixé à **{{salaire_brut:number}} €**.

## Article 3 - Durée du travail

La durée hebdomadaire de travail est de **{{heures_semaine:number}} heures**.

## Article 4 - Période d'essai

La période d'essai est de **{{periode_essai:select:1 mois,2 mois,3 mois,4 mois}}**.
MARKDOWN);
        $templateTravail->setVariablesJson([
            ['name' => 'employeur_nom', 'type' => 'text', 'required' => true, 'label' => 'Nom de l\'employeur'],
            ['name' => 'salarie_nom', 'type' => 'text', 'required' => true, 'label' => 'Nom du salarié'],
            ['name' => 'poste', 'type' => 'text', 'required' => true, 'label' => 'Poste'],
            ['name' => 'date_debut', 'type' => 'date', 'required' => true, 'label' => 'Date de début'],
            ['name' => 'salaire_brut', 'type' => 'number', 'required' => true, 'label' => 'Salaire brut mensuel (€)'],
            ['name' => 'heures_semaine', 'type' => 'number', 'required' => true, 'label' => 'Heures par semaine'],
            ['name' => 'periode_essai', 'type' => 'select', 'required' => true, 'label' => 'Période d\'essai', 'options' => ['1 mois', '2 mois', '3 mois', '4 mois']],
        ]);
        $manager->persist($templateTravail);

        // ========== DOCUMENTS ==========

        // Document 1 - NDA Draft
        $doc1 = new Document();
        $doc1->setTemplate($templateNda);
        $doc1->setOrganization($org1);
        $doc1->setCreatedBy($editor);
        $doc1->setStatus(Document::STATUS_DRAFT);
        $doc1->setCreatedAt(new \DateTimeImmutable('-5 months'));
        $doc1->setDataJson([
            'partie_1_nom' => 'ACME Technologies',
            'partie_1_forme_juridique' => 'SAS',
            'partie_1_adresse' => '123 rue de l\'Innovation, 75001 Paris',
            'partie_1_representant' => 'Jean Dupont, Président',
            'partie_2_nom' => 'DevStudio',
            'partie_2_forme_juridique' => 'SARL',
            'partie_2_adresse' => '45 avenue du Code, 69001 Lyon',
            'partie_2_representant' => 'Marie Martin, Gérante',
            'objet_collaboration' => 'développement d\'une application mobile de gestion RH',
            'duree_confidentialite' => '3',
            'lieu_signature' => 'Paris',
            'date_signature' => '2025-01-15',
        ]);
        $doc1->setGeneratedContent($this->generateContent($templateNda, $doc1->getDataJson()));
        $manager->persist($doc1);

        // Document 2 - NDA En revue
        $doc2 = new Document();
        $doc2->setTemplate($templateNda);
        $doc2->setOrganization($org1);
        $doc2->setCreatedBy($editor);
        $doc2->setStatus(Document::STATUS_REVIEW);
        $doc2->setCreatedAt(new \DateTimeImmutable('-4 months'));
        $doc2->setDataJson([
            'partie_1_nom' => 'TechVision SA',
            'partie_1_forme_juridique' => 'Société Anonyme',
            'partie_1_adresse' => '100 avenue des Champs-Élysées, 75008 Paris',
            'partie_1_representant' => 'M. Philippe Bernard, Directeur Général',
            'partie_2_nom' => 'CloudServices SAS',
            'partie_2_forme_juridique' => 'Société par Actions Simplifiée',
            'partie_2_adresse' => '25 rue du Cloud, 92100 Boulogne',
            'partie_2_representant' => 'Mme Sophie Leroy, Présidente',
            'objet_collaboration' => 'l\'hébergement et la maintenance de l\'infrastructure cloud',
            'duree_confidentialite' => '5',
            'lieu_signature' => 'Paris',
            'date_signature' => '2025-01-20',
        ]);
        $doc2->setGeneratedContent($this->generateContent($templateNda, $doc2->getDataJson()));
        $manager->persist($doc2);

        // Document 3 - CGV Approuvé
        $doc3 = new Document();
        $doc3->setTemplate($templateCgv);
        $doc3->setOrganization($org1);
        $doc3->setCreatedBy($editor);
        $doc3->setStatus(Document::STATUS_APPROVED);
        $doc3->setCreatedAt(new \DateTimeImmutable('-3 months'));
        $doc3->setDataJson([
            'entreprise_nom' => 'WebAgency Pro',
            'entreprise_forme_juridique' => 'SAS',
            'entreprise_capital' => '50000',
            'entreprise_adresse' => '8 boulevard Haussmann, 75009 Paris',
            'entreprise_siret' => '123 456 789 00012',
            'type_prestation' => 'développement web et maintenance',
            'prix_ht_ttc' => 'HT',
            'tarif_horaire' => '85',
            'delai_paiement' => '30',
            'mode_paiement' => 'Virement',
            'date_redaction' => '2025-01-10',
        ]);
        $doc3->setGeneratedContent($this->generateContent($templateCgv, $doc3->getDataJson()));
        $manager->persist($doc3);

        // Document 4 - Contrat SaaS Signé
        $doc4 = new Document();
        $doc4->setTemplate($templateSaas);
        $doc4->setOrganization($org1);
        $doc4->setCreatedBy($editor);
        $doc4->setStatus(Document::STATUS_SIGNED);
        $doc4->setCreatedAt(new \DateTimeImmutable('-2 months'));
        $doc4->setDataJson([
            'client_nom' => 'Startup Innov\'',
            'duree_contrat' => '12',
            'date_debut' => '2025-02-01',
            'montant_mensuel' => '299',
            'mode_paiement' => 'Prélèvement automatique',
        ]);
        $doc4->setGeneratedContent($this->generateContent($templateSaas, $doc4->getDataJson()));
        $manager->persist($doc4);

        // Document 5 - Contrat SaaS Archivé
        $doc5 = new Document();
        $doc5->setTemplate($templateSaas);
        $doc5->setOrganization($org1);
        $doc5->setCreatedBy($editor);
        $doc5->setStatus(Document::STATUS_ARCHIVED);
        $doc5->setCreatedAt(new \DateTimeImmutable('-1 month'));
        $doc5->setDataJson([
            'client_nom' => 'OldClient SARL',
            'duree_contrat' => '6',
            'date_debut' => '2024-06-01',
            'montant_mensuel' => '199',
            'mode_paiement' => 'Virement',
        ]);
        $doc5->setGeneratedContent($this->generateContent($templateSaas, $doc5->getDataJson()));
        $manager->persist($doc5);

        // Document 6 - NDA récent
        $doc6 = new Document();
        $doc6->setTemplate($templateNda);
        $doc6->setOrganization($org1);
        $doc6->setCreatedBy($editor);
        $doc6->setStatus(Document::STATUS_DRAFT);
        $doc6->setCreatedAt(new \DateTimeImmutable('-2 weeks'));
        $doc6->setDataJson([
            'partie_1_nom' => 'InnoTech Labs',
            'partie_1_forme_juridique' => 'SAS',
            'partie_1_adresse' => '50 rue de la Recherche, 31000 Toulouse',
            'partie_1_representant' => 'Dr. Anne Moreau, Directrice R&D',
            'partie_2_nom' => 'DataSecure',
            'partie_2_forme_juridique' => 'SARL',
            'partie_2_adresse' => '12 rue des Serveurs, 33000 Bordeaux',
            'partie_2_representant' => 'M. Thomas Blanc, Gérant',
            'objet_collaboration' => 'audit de sécurité informatique',
            'duree_confidentialite' => '10',
            'lieu_signature' => 'Toulouse',
            'date_signature' => '2025-12-01',
        ]);
        $doc6->setGeneratedContent($this->generateContent($templateNda, $doc6->getDataJson()));
        $manager->persist($doc6);

        // Document 7 - CGV récent
        $doc7 = new Document();
        $doc7->setTemplate($templateCgv);
        $doc7->setOrganization($org1);
        $doc7->setCreatedBy($validator);
        $doc7->setStatus(Document::STATUS_REVIEW);
        $doc7->setCreatedAt(new \DateTimeImmutable('-5 days'));
        $doc7->setDataJson([
            'entreprise_nom' => 'DesignStudio',
            'entreprise_forme_juridique' => 'EURL',
            'entreprise_capital' => '10000',
            'entreprise_adresse' => '22 rue des Arts, 13001 Marseille',
            'entreprise_siret' => '987 654 321 00045',
            'type_prestation' => 'création graphique et identité visuelle',
            'prix_ht_ttc' => 'HT',
            'tarif_horaire' => '65',
            'delai_paiement' => '45',
            'mode_paiement' => 'Chèque',
            'date_redaction' => '2025-12-07',
        ]);
        $doc7->setGeneratedContent($this->generateContent($templateCgv, $doc7->getDataJson()));
        $manager->persist($doc7);

        // Document 8 - Document aujourd'hui
        $doc8 = new Document();
        $doc8->setTemplate($templateSaas);
        $doc8->setOrganization($org1);
        $doc8->setCreatedBy($editor);
        $doc8->setStatus(Document::STATUS_DRAFT);
        $doc8->setCreatedAt(new \DateTimeImmutable('now'));
        $doc8->setDataJson([
            'client_nom' => 'FutureTech',
            'duree_contrat' => '24',
            'date_debut' => '2026-01-01',
            'montant_mensuel' => '499',
            'mode_paiement' => 'Carte bancaire',
        ]);
        $doc8->setGeneratedContent($this->generateContent($templateSaas, $doc8->getDataJson()));
        $manager->persist($doc8);

        // Document 9 - Cabinet Martin
        $doc9 = new Document();
        $doc9->setTemplate($templateTravail);
        $doc9->setOrganization($org2);
        $doc9->setCreatedBy($user);
        $doc9->setStatus(Document::STATUS_SIGNED);
        $doc9->setCreatedAt(new \DateTimeImmutable('-3 months'));
        $doc9->setDataJson([
            'employeur_nom' => 'Cabinet Martin',
            'salarie_nom' => 'Julie Petit',
            'poste' => 'Juriste',
            'date_debut' => '2025-10-01',
            'salaire_brut' => '3500',
            'heures_semaine' => '35',
            'periode_essai' => '3 mois',
        ]);
        $doc9->setGeneratedContent($this->generateContent($templateTravail, $doc9->getDataJson()));
        $manager->persist($doc9);

        // Document 10 - Cabinet Martin
        $doc10 = new Document();
        $doc10->setTemplate($templateTravail);
        $doc10->setOrganization($org2);
        $doc10->setCreatedBy($user);
        $doc10->setStatus(Document::STATUS_DRAFT);
        $doc10->setCreatedAt(new \DateTimeImmutable('-1 month'));
        $doc10->setDataJson([
            'employeur_nom' => 'Cabinet Martin',
            'salarie_nom' => 'Marc Durand',
            'poste' => 'Assistant juridique',
            'date_debut' => '2026-01-15',
            'salaire_brut' => '2200',
            'heures_semaine' => '35',
            'periode_essai' => '2 mois',
        ]);
        $doc10->setGeneratedContent($this->generateContent($templateTravail, $doc10->getDataJson()));
        $manager->persist($doc10);

        $manager->flush();
    }

    private function generateContent(Template $template, array $data): string
    {
        $content = $template->getBodyMarkdown();

        // Formatter les dates en français
        $formatter = new \IntlDateFormatter(
            'fr_FR',
            \IntlDateFormatter::LONG,
            \IntlDateFormatter::NONE
        );

        foreach ($data as $key => $value) {
            // Détecter si c'est une date (format YYYY-MM-DD)
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                $date = new \DateTime($value);
                $value = $formatter->format($date);
            }

            // Remplacer {{key}}, {{key:type}} et {{key:select:options}}
            $content = preg_replace('/\{\{' . preg_quote($key, '/') . '(:[^}]+)?\}\}/', $value, $content);
        }

        return $content;
    }
}
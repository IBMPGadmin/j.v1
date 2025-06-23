<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LegalKeyTerm;

class LegalKeyTermsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $terms = [
            // Immigration Law Terms - English
            [
                'term' => 'Permanent Resident',
                'definition' => 'A person who has been granted permanent residence status in Canada but is not a Canadian citizen.',
                'language' => 'en',
                'category' => 'Immigration Law',
                'source' => 'IRCC',
                'status' => 'active'
            ],
            [
                'term' => 'Express Entry',
                'definition' => 'An online system used to manage immigration applications from skilled workers.',
                'language' => 'en',
                'category' => 'Immigration Law',
                'source' => 'IRCC',
                'status' => 'active'
            ],

            // Immigration Law Terms - French
            [
                'term' => 'Résident Permanent',
                'definition' => 'Une personne qui a obtenu le statut de résident permanent au Canada mais qui n\'est pas un citoyen canadien.',
                'language' => 'fr',
                'category' => 'Immigration Law',
                'source' => 'IRCC',
                'status' => 'active'
            ],
            [
                'term' => 'Entrée Express',
                'definition' => 'Un système en ligne utilisé pour gérer les demandes d\'immigration des travailleurs qualifiés.',
                'language' => 'fr',
                'category' => 'Immigration Law',
                'source' => 'IRCC',
                'status' => 'active'
            ],

            // Corporate Law Terms - English
            [
                'term' => 'Articles of Incorporation',
                'definition' => 'Legal documents that establish the existence of a corporation.',
                'language' => 'en',
                'category' => 'Corporate Law',
                'source' => 'Canadian Business Corporations Act',
                'status' => 'active'
            ],
            [
                'term' => 'Bylaws',
                'definition' => 'Rules established by an organization to regulate itself.',
                'language' => 'en',
                'category' => 'Corporate Law',
                'source' => 'Canadian Business Corporations Act',
                'status' => 'active'
            ],

            // Administrative Law Terms - English
            [
                'term' => 'Administrative Tribunal',
                'definition' => 'A body established by legislation to hear and decide disputes.',
                'language' => 'en',
                'category' => 'Administrative Law',
                'source' => 'Canadian Administrative Law',
                'status' => 'active'
            ],

            // Immigration Terms - Spanish
            [
                'term' => 'Residente Permanente',
                'definition' => 'Una persona que ha recibido el estatus de residente permanente en Canadá pero no es ciudadano canadiense.',
                'language' => 'es',
                'category' => 'Immigration Law',
                'source' => 'IRCC',
                'status' => 'active'
            ],

            // Immigration Terms - Chinese
            [
                'term' => '永久居民',
                'definition' => '已获得加拿大永久居民身份但尚未成为加拿大公民的人。',
                'language' => 'zh',
                'category' => 'Immigration Law',
                'source' => 'IRCC',
                'status' => 'active'
            ],

            // Key Immigration Terms - English
            [
                'term' => 'LMIA',
                'definition' => 'Labour Market Impact Assessment - A document that an employer in Canada may need to get before hiring a foreign worker.',
                'language' => 'en',
                'category' => 'Immigration Law',
                'source' => 'IRCC',
                'status' => 'active'
            ],
            [
                'term' => 'Work Permit',
                'definition' => 'A document that allows foreign nationals to work for specific employers in Canada.',
                'language' => 'en',
                'category' => 'Immigration Law',
                'source' => 'IRCC',
                'status' => 'active'
            ],
            [
                'term' => 'Study Permit',
                'definition' => 'A document that allows foreign nationals to study at designated learning institutions in Canada.',
                'language' => 'en',
                'category' => 'Immigration Law',
                'source' => 'IRCC',
                'status' => 'active'
            ],

            // More Immigration Terms - French
            [
                'term' => 'Permis de Travail',
                'definition' => 'Un document qui permet aux ressortissants étrangers de travailler pour des employeurs spécifiques au Canada.',
                'language' => 'fr',
                'category' => 'Immigration Law',
                'source' => 'IRCC',
                'status' => 'active'
            ],
            [
                'term' => 'Permis d\'Études',
                'definition' => 'Un document qui permet aux ressortissants étrangers d\'étudier dans des établissements d\'enseignement désignés au Canada.',
                'language' => 'fr',
                'category' => 'Immigration Law',
                'source' => 'IRCC',
                'status' => 'active'
            ],

            // Legal Process Terms - English
            [
                'term' => 'Power of Attorney',
                'definition' => 'Legal authorization to act on someone else\'s behalf in legal or business matters.',
                'language' => 'en',
                'category' => 'General Legal Terms',
                'source' => 'Canadian Legal System',
                'status' => 'active'
            ],
            [
                'term' => 'Affidavit',
                'definition' => 'A written statement confirmed by oath or affirmation for use as evidence in court.',
                'language' => 'en',
                'category' => 'General Legal Terms',
                'source' => 'Canadian Legal System',
                'status' => 'active'
            ],

            // Immigration Status Terms - English
            [
                'term' => 'Temporary Resident',
                'definition' => 'A foreign national who is legally authorized to enter Canada for temporary purposes.',
                'language' => 'en',
                'category' => 'Immigration Law',
                'source' => 'IRCC',
                'status' => 'active'
            ],
            [
                'term' => 'Protected Person',
                'definition' => 'A person who has been determined to be a Convention refugee or person in need of protection.',
                'language' => 'en',
                'category' => 'Immigration Law',
                'source' => 'IRCC',
                'status' => 'active'
            ],

            // Program-Specific Terms - English
            [
                'term' => 'Provincial Nominee Program',
                'definition' => 'Programs that allow provinces and territories to nominate immigrants to meet their economic needs.',
                'language' => 'en',
                'category' => 'Immigration Law',
                'source' => 'IRCC',
                'status' => 'active'
            ],
            [
                'term' => 'Canadian Experience Class',
                'definition' => 'An immigration program for people who have Canadian work experience.',
                'language' => 'en',
                'category' => 'Immigration Law',
                'source' => 'IRCC',
                'status' => 'active'
            ]
        ];

        foreach ($terms as $term) {
            LegalKeyTerm::create($term);
        }
    }
}

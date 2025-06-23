<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GovernmentLink;

class GovernmentLinkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $links = [
            // Federal Government Links
            [
                'name' => 'Immigration, Refugees and Citizenship Canada (IRCC)',
                'url' => 'https://www.canada.ca/en/immigration-refugees-citizenship.html',
                'category' => 'Federal',
                'description' => 'Main department for immigration and citizenship services in Canada',
                'active' => true,
                'sort_order' => 10,
            ],
            [
                'name' => 'IRCC - Permanent Residence Applications',
                'url' => 'https://www.canada.ca/en/immigration-refugees-citizenship/services/immigrate-canada.html',
                'category' => 'Federal',
                'description' => 'Information on permanent residence applications and pathways',
                'active' => true,
                'sort_order' => 11,
            ],
            [
                'name' => 'IRCC - Express Entry',
                'url' => 'https://www.canada.ca/en/immigration-refugees-citizenship/services/immigrate-canada/express-entry.html',
                'category' => 'Federal',
                'description' => 'Express Entry system for skilled immigrants',
                'active' => true,
                'sort_order' => 12,
            ],
            [
                'name' => 'IRCC - Family Sponsorship',
                'url' => 'https://www.canada.ca/en/immigration-refugees-citizenship/services/immigrate-canada/family-sponsorship.html',
                'category' => 'Federal',
                'description' => 'Sponsorship programs for family members',
                'active' => true,
                'sort_order' => 13,
            ],
            [
                'name' => 'IRCC - Study Permits',
                'url' => 'https://www.canada.ca/en/immigration-refugees-citizenship/services/study-canada.html',
                'category' => 'Federal',
                'description' => 'Information on studying in Canada and study permits',
                'active' => true,
                'sort_order' => 14,
            ],
            [
                'name' => 'IRCC - Work Permits',
                'url' => 'https://www.canada.ca/en/immigration-refugees-citizenship/services/work-canada.html',
                'category' => 'Federal',
                'description' => 'Information on working in Canada and work permits',
                'active' => true,
                'sort_order' => 15,
            ],
            [
                'name' => 'IRCC - Citizenship',
                'url' => 'https://www.canada.ca/en/immigration-refugees-citizenship/services/canadian-citizenship.html',
                'category' => 'Federal',
                'description' => 'Information on Canadian citizenship applications and requirements',
                'active' => true,
                'sort_order' => 16,
            ],
            [
                'name' => 'Canada Border Services Agency (CBSA)',
                'url' => 'https://www.cbsa-asfc.gc.ca/menu-eng.html',
                'category' => 'Federal',
                'description' => 'Border services and enforcement agency',
                'active' => true,
                'sort_order' => 20,
            ],
            [
                'name' => 'Department of Justice Canada',
                'url' => 'https://www.justice.gc.ca/eng/',
                'category' => 'Federal',
                'description' => 'Federal department responsible for justice system',
                'active' => true,
                'sort_order' => 30,
            ],
            [
                'name' => 'Supreme Court of Canada',
                'url' => 'https://www.scc-csc.ca/home-accueil/index-eng.aspx',
                'category' => 'Federal',
                'description' => 'Highest court in Canada\'s judicial system',
                'active' => true,
                'sort_order' => 40,
            ],
            [
                'name' => 'Federal Court of Canada',
                'url' => 'https://www.fct-cf.gc.ca/en/home',
                'category' => 'Federal',
                'description' => 'Court that hears cases on immigration, citizenship, and other federal matters',
                'active' => true,
                'sort_order' => 50,
            ],
            [
                'name' => 'Immigration and Refugee Board of Canada (IRB)',
                'url' => 'https://irb-cisr.gc.ca/en/Pages/index.aspx',
                'category' => 'Federal',
                'description' => 'Canada\'s largest independent administrative tribunal',
                'active' => true,
                'sort_order' => 60,
            ],
            [
                'name' => 'Employment and Social Development Canada (ESDC)',
                'url' => 'https://www.canada.ca/en/employment-social-development.html',
                'category' => 'Federal',
                'description' => 'Department responsible for Labour Market Impact Assessments (LMIAs)',
                'active' => true,
                'sort_order' => 70,
            ],
            [
                'name' => 'Global Affairs Canada',
                'url' => 'https://www.international.gc.ca/global-affairs-affaires-mondiales/home-accueil.aspx?lang=eng',
                'category' => 'Federal',
                'description' => 'Department managing diplomatic and consular relations',
                'active' => true,
                'sort_order' => 80,
            ],
            
            // Provincial Government Links
            [
                'name' => 'Ontario Immigration',
                'url' => 'https://www.ontario.ca/page/immigration-ontario',
                'category' => 'Provincial',
                'description' => 'Immigration programs for Ontario',
                'active' => true,
                'sort_order' => 100,
            ],
            [
                'name' => 'Quebec Immigration',
                'url' => 'https://www.quebec.ca/en/immigration',
                'category' => 'Provincial',
                'description' => 'Immigration programs for Quebec',
                'active' => true,
                'sort_order' => 110,
            ],
            [
                'name' => 'British Columbia Provincial Nominee Program (BC PNP)',
                'url' => 'https://www.welcomebc.ca/Immigrate-to-B-C/BC-PNP',
                'category' => 'Provincial',
                'description' => 'Immigration programs for British Columbia',
                'active' => true,
                'sort_order' => 120,
            ],
            [
                'name' => 'Alberta Advantage Immigration Program',
                'url' => 'https://www.alberta.ca/alberta-advantage-immigration-program.aspx',
                'category' => 'Provincial',
                'description' => 'Immigration programs for Alberta',
                'active' => true,
                'sort_order' => 130,
            ],
            [
                'name' => 'Manitoba Provincial Nominee Program',
                'url' => 'https://immigratemanitoba.com/',
                'category' => 'Provincial',
                'description' => 'Immigration programs for Manitoba',
                'active' => true,
                'sort_order' => 140,
            ],
            [
                'name' => 'Saskatchewan Immigrant Nominee Program (SINP)',
                'url' => 'https://www.saskatchewan.ca/residents/moving-to-saskatchewan/immigrating-to-saskatchewan',
                'category' => 'Provincial',
                'description' => 'Immigration programs for Saskatchewan',
                'active' => true,
                'sort_order' => 150,
            ],
            [
                'name' => 'Nova Scotia Provincial Nominee Program',
                'url' => 'https://novascotiaimmigration.com/',
                'category' => 'Provincial',
                'description' => 'Immigration programs for Nova Scotia',
                'active' => true,
                'sort_order' => 160,
            ],
            [
                'name' => 'New Brunswick Provincial Nominee Program',
                'url' => 'https://www.welcomenb.ca/content/wel-bien/en/immigrating_and_settling/how_to_immigrate/new_brunswick_provincialnomineeprogram.html',
                'category' => 'Provincial',
                'description' => 'Immigration programs for New Brunswick',
                'active' => true,
                'sort_order' => 170,
            ],
            
            // Legal Resources and References
            [
                'name' => 'Canadian Legal Information Institute (CanLII)',
                'url' => 'https://www.canlii.org/en/',
                'category' => 'Legal Resources',
                'description' => 'Free access to Canadian law',
                'active' => true,
                'sort_order' => 200,
            ],
            [
                'name' => 'Immigration and Refugee Protection Act (IRPA)',
                'url' => 'https://laws.justice.gc.ca/eng/acts/i-2.5/',
                'category' => 'Legal Resources',
                'description' => 'Primary legislation for immigration and refugee protection in Canada',
                'active' => true,
                'sort_order' => 210,
            ],
            [
                'name' => 'Immigration and Refugee Protection Regulations (IRPR)',
                'url' => 'https://laws.justice.gc.ca/eng/regulations/SOR-2002-227/',
                'category' => 'Legal Resources',
                'description' => 'Regulations associated with the Immigration and Refugee Protection Act',
                'active' => true,
                'sort_order' => 220,
            ],
            [
                'name' => 'Citizenship Act',
                'url' => 'https://laws.justice.gc.ca/eng/acts/C-29/',
                'category' => 'Legal Resources',
                'description' => 'Legislation governing Canadian citizenship',
                'active' => true,
                'sort_order' => 230,
            ],
            [
                'name' => 'IRCC Program Delivery Instructions',
                'url' => 'https://www.canada.ca/en/immigration-refugees-citizenship/corporate/publications-manuals/operational-bulletins-manuals.html',
                'category' => 'Legal Resources',
                'description' => 'Operational instructions and guidelines for immigration officers',
                'active' => true,
                'sort_order' => 240,
            ],
            
            // Professional Associations
            [
                'name' => 'Canadian Bar Association (CBA)',
                'url' => 'https://www.cba.org/',
                'category' => 'Professional Associations',
                'description' => 'National association representing Canadian legal professionals',
                'active' => true,
                'sort_order' => 300,
            ],
            [
                'name' => 'Law Society of Ontario',
                'url' => 'https://lso.ca/',
                'category' => 'Professional Associations',
                'description' => 'Regulatory body for lawyers and paralegals in Ontario',
                'active' => true,
                'sort_order' => 310,
            ],
            [
                'name' => 'Canadian Association of Immigration Consultants (CICC)',
                'url' => 'https://college-ic.ca/',
                'category' => 'Professional Associations',
                'description' => 'National regulatory body for licensed immigration consultants',
                'active' => true,
                'sort_order' => 320,
            ],
            
            // International Organizations
            [
                'name' => 'United Nations High Commissioner for Refugees (UNHCR)',
                'url' => 'https://www.unhcr.org/canada.html',
                'category' => 'International',
                'description' => 'UN agency mandated to protect refugees',
                'active' => true,
                'sort_order' => 400,
            ],
            [
                'name' => 'International Organization for Migration (IOM)',
                'url' => 'https://www.iom.int/countries/canada',
                'category' => 'International',
                'description' => 'Leading international organization for migration',
                'active' => true,
                'sort_order' => 410,
            ],
        ];

        foreach ($links as $link) {
            GovernmentLink::create($link);
        }
    }
}

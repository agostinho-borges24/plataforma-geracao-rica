<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $now = now();

        $countries = collect($this->countries())
            ->map(fn (array $c) => [
                'iso_code' => $c[0],
                'name' => $c[1],
                'currency_code' => $c[2],
                'phone_code' => $c[3],
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->all();

        // upsert em lote: idempotente, não duplica se o seeder rodar de novo.
        Country::query()->upsert(
            $countries,
            uniqueBy: ['iso_code'],
            update: ['name', 'currency_code', 'phone_code', 'active', 'updated_at'],
        );
    }

    /**
     * @return array<int, array{0: string, 1: string, 2: string, 3: string}>
     *                                                                        [iso_code, name, currency_code, phone_code]
     */
    protected function countries(): array
    {
        return [
            // --- África (prioridade: PALOP e vizinhos) ---
            ['AO', 'Angola', 'AOA', '+244'],
            ['PT', 'Portugal', 'EUR', '+351'],
            ['MZ', 'Moçambique', 'MZN', '+258'],
            ['CV', 'Cabo Verde', 'CVE', '+238'],
            ['GW', 'Guiné-Bissau', 'XOF', '+245'],
            ['ST', 'São Tomé e Príncipe', 'STN', '+239'],
            ['ZA', 'África do Sul', 'ZAR', '+27'],
            ['NA', 'Namíbia', 'NAD', '+264'],
            ['ZM', 'Zâmbia', 'ZMW', '+260'],
            ['ZW', 'Zimbabué', 'USD', '+263'],
            ['CD', 'República Democrática do Congo', 'CDF', '+243'],
            ['CG', 'República do Congo', 'XAF', '+242'],
            ['NG', 'Nigéria', 'NGN', '+234'],
            ['GH', 'Gana', 'GHS', '+233'],
            ['KE', 'Quénia', 'KES', '+254'],
            ['TZ', 'Tanzânia', 'TZS', '+255'],
            ['UG', 'Uganda', 'UGX', '+256'],
            ['ET', 'Etiópia', 'ETB', '+251'],
            ['SN', 'Senegal', 'XOF', '+221'],
            ['CI', 'Costa do Marfim', 'XOF', '+225'],
            ['CM', 'Camarões', 'XAF', '+237'],
            ['EG', 'Egito', 'EGP', '+20'],
            ['MA', 'Marrocos', 'MAD', '+212'],
            ['DZ', 'Argélia', 'DZD', '+213'],
            ['TN', 'Tunísia', 'TND', '+216'],
            ['BW', 'Botsuana', 'BWP', '+267'],

            // --- Europa ---
            ['ES', 'Espanha', 'EUR', '+34'],
            ['FR', 'França', 'EUR', '+33'],
            ['DE', 'Alemanha', 'EUR', '+49'],
            ['IT', 'Itália', 'EUR', '+39'],
            ['GB', 'Reino Unido', 'GBP', '+44'],
            ['NL', 'Países Baixos', 'EUR', '+31'],
            ['BE', 'Bélgica', 'EUR', '+32'],
            ['CH', 'Suíça', 'CHF', '+41'],
            ['SE', 'Suécia', 'SEK', '+46'],
            ['NO', 'Noruega', 'NOK', '+47'],
            ['DK', 'Dinamarca', 'DKK', '+45'],
            ['IE', 'Irlanda', 'EUR', '+353'],
            ['PL', 'Polónia', 'PLN', '+48'],
            ['AT', 'Áustria', 'EUR', '+43'],
            ['LU', 'Luxemburgo', 'EUR', '+352'],
            ['RO', 'Roménia', 'RON', '+40'],
            ['GR', 'Grécia', 'EUR', '+30'],
            ['UA', 'Ucrânia', 'UAH', '+380'],

            // --- Américas ---
            ['US', 'Estados Unidos', 'USD', '+1'],
            ['CA', 'Canadá', 'CAD', '+1'],
            ['BR', 'Brasil', 'BRL', '+55'],
            ['MX', 'México', 'MXN', '+52'],
            ['AR', 'Argentina', 'ARS', '+54'],
            ['CL', 'Chile', 'CLP', '+56'],
            ['CO', 'Colômbia', 'COP', '+57'],
            ['PE', 'Peru', 'PEN', '+51'],
            ['UY', 'Uruguai', 'UYU', '+598'],
            ['VE', 'Venezuela', 'VES', '+58'],

            // --- Ásia ---
            ['CN', 'China', 'CNY', '+86'],
            ['JP', 'Japão', 'JPY', '+81'],
            ['KR', 'Coreia do Sul', 'KRW', '+82'],
            ['IN', 'Índia', 'INR', '+91'],
            ['ID', 'Indonésia', 'IDR', '+62'],
            ['PH', 'Filipinas', 'PHP', '+63'],
            ['VN', 'Vietname', 'VND', '+84'],
            ['TH', 'Tailândia', 'THB', '+66'],
            ['MY', 'Malásia', 'MYR', '+60'],
            ['SG', 'Singapura', 'SGD', '+65'],
            ['AE', 'Emirados Árabes Unidos', 'AED', '+971'],
            ['SA', 'Arábia Saudita', 'SAR', '+966'],
            ['IL', 'Israel', 'ILS', '+972'],
            ['TR', 'Turquia', 'TRY', '+90'],
            ['PK', 'Paquistão', 'PKR', '+92'],
            ['BD', 'Bangladesh', 'BDT', '+880'],

            // --- Oceania ---
            ['AU', 'Austrália', 'AUD', '+61'],
            ['NZ', 'Nova Zelândia', 'NZD', '+64'],
        ];
    }
}
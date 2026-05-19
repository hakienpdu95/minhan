<?php

namespace App\View\Components;

use App\Models\Province;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\Component;

class AddressPicker extends Component
{
    public Collection $provinces;

    /**
     * @param string|null $provinceValue  Pre-selected province_code
     * @param string|null $wardValue      Pre-selected ward_code
     * @param string|null $addressValue   Pre-filled street address
     * @param bool        $required       Show asterisk + server-side required hint
     * @param string      $instanceId     Unique suffix so multiple pickers per page don't clash
     * @param string      $nameProvince   <select name="..."> for province
     * @param string      $nameWard       <select name="..."> for ward
     * @param string      $nameAddress    <input  name="..."> for street address
     * @param bool        $showAddress    Whether to render the street address row
     */
    public function __construct(
        public ?string $provinceValue = null,
        public ?string $wardValue     = null,
        public ?string $addressValue  = null,
        public bool    $required      = false,
        public string  $instanceId    = 'addr',
        public string  $nameProvince  = 'province_code',
        public string  $nameWard      = 'ward_code',
        public string  $nameAddress   = 'address',
        public bool    $showAddress   = true,
    ) {
        $this->provinces = Province::orderBy('name')
            ->get(['province_code', 'name', 'place_type']);
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('components.address-picker');
    }
}

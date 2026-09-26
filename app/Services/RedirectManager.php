<?php

namespace App\Services;

use App\Models\Redirect;

class RedirectManager
{
    public function record(string $oldPath, string $newPath): void
    {
        if ($oldPath === $newPath) {
            return;
        }
        $redirect = Redirect::query()->where('source_path', $oldPath)->first() ?? new Redirect;
        $redirect->fill(['source_path' => $oldPath, 'destination_path' => $newPath]);
        $redirect->forceFill(['status_code' => 301, 'is_active' => true])->save();
        Redirect::query()->where('destination_path', $oldPath)->where('source_path', '!=', $newPath)->get()->each(function (Redirect $existing) use ($newPath): void {
            $existing->destination_path = $newPath;
            $existing->save();
        });
    }
}

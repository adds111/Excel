<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelController extends Controller
{
    public function download(Request $req) : StreamedResponse
    {
        $value = $req->input('file');

        return Storage::download($value);
    }

    public function upload(Request $req) : View
    {
        $file = new FileHandler();

        $files = Storage::Files('/excel');

        $fileName = $req->file('file');

        if ($fileName == null) {
            return view('welcome', ['files' => $files])->withErrors(['error' => 'Вы ничего не отправили']);
        }

        $path = [];

        for ($i = 0; $i <= count($fileName) - 1; $i++) {
            if (str_contains($fileName[$i]->getClientOriginalName(), '.xlsx')) {
                $path[] = Storage::putFileAs(
                    'excel', $req->file('file')[$i], $fileName[$i]->getClientOriginalName()
                );

            } else {
                return view('welcome', ['files' => $files])->withErrors(['error' => 'Файлы не xlsx']);
            }
        }

        if ($path) {
            $file->convert($path);

        } else {
            return view('welcome', ['files' => $files])->withErrors(['error' => 'ошибка при сохранении файла']);
        }

        $megafiles = Storage::Files('/excel');

        for ($i = 0; $i < count($megafiles); $i++) {
            if (!str_contains($megafiles[$i], 'f.xlsx')) {
                Storage::delete($megafiles[$i]);
            }
        }

        return view('welcome', [
            'files' => Storage::Files('/excel')
        ])->with('success', 'файл загружен успешно');
    }
}

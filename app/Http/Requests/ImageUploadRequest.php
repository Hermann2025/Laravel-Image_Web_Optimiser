<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImageUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Si c'est un tableau de fichiers (uploadMultiple)
        if ($this->hasFile('images') && is_array($this->file('images'))) {
            return [
                'images' => 'required|array|max:20',
                'images.*' => 'required|file|mimes:jpeg,png,gif,webp,bmp,zip|max:307200',
            ];
        }

        // Si c'est un fichier unique (Dropzone sans uploadMultiple ou fichier unique)
        if ($this->hasFile('images')) {
            return [
                'images' => 'required|file|mimes:jpeg,png,gif,webp,bmp,zip|max:307200',
            ];
        }

        // Fallback : tableau avec clés fichiers
        return [
                'images' => 'required|array|max:20',
                'images.*' => 'required|file|mimes:jpeg,png,gif,webp,bmp,zip|max:307200',
        ];
    }

    public function messages(): array
    {
        return [
            'images.required' => 'Veuillez sélectionner au moins une image.',
            'images.max' => 'Vous ne pouvez pas uploader plus de 20 fichiers à la fois.',
            'images.*.mimes' => 'Seuls les formats JPG, PNG, GIF, WebP, BMP et ZIP sont acceptés.',
            'images.*.max' => 'Chaque fichier ne doit pas dépasser 300 Mo.',
        ];
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::when($request->q, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        return view('admin.products.form', ['product' => new Product(['size_ml' => 50, 'is_active' => true])]);
    }

    public function store(Request $request)
    {
        Product::create($this->validated($request));

        return redirect()->route('admin.products.index')->with('success', 'Product created.');
    }

    public function edit(Product $product)
    {
        return view('admin.products.form', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $product->update($this->validated($request, $product));

        return redirect()->route('admin.products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $this->deleteImage($product);
        $product->delete();

        return back()->with('success', 'Product deleted.');
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'category' => 'nullable|string|max:80',
            'short_description' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'notes' => 'nullable|string|max:255',
            'size_ml' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'old_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|max:4096',
        ]);

        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active');

        // Images are saved directly in public/uploads/products (easy on any hosting)
        if ($request->hasFile('image')) {
            if ($product) {
                $this->deleteImage($product);
            }
            $file = $request->file('image');
            $name = Str::slug($data['name']).'-'.time().'.'.$file->extension();
            $file->move(public_path('uploads/products'), $name);
            $data['image'] = 'uploads/products/'.$name;
        } else {
            unset($data['image']);
        }

        return $data;
    }

    private function deleteImage(Product $product): void
    {
        if ($product->image && str_starts_with($product->image, 'uploads/')) {
            File::delete(public_path($product->image));
        }
    }
}

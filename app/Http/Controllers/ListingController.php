<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Validation\Rule;
use MongoDB\Driver\Session;

class ListingController extends Controller
{
    /**
     * Show all Listing
     *
     * @return Application|Factory|View|\Illuminate\Foundation\Application
     */
    public function index(): \Illuminate\Foundation\Application|View|Factory|Application
    {
        return view(
            'listings.index',
            [
                'listings' => Listing::latest()->filter(request(['tag', 'search']))->paginate(6),
            ]
        );
    }

    /**
     * Show single Listing
     *
     * @param Listing $listing
     * @return Application|Factory|View|\Illuminate\Foundation\Application
     */
    public function show(Listing $listing): \Illuminate\Foundation\Application|View|Factory|Application
    {
        return view('listings.show', [
            'listing' => $listing,
        ]);
    }

    /**
     * @param Listing $listing
     * @return View|\Illuminate\Foundation\Application|Factory|Application
     */
    public function edit(Listing $listing): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('listings.edit', ['listing' => $listing]);
    }

    /**
     * Update Listing Data
     *
     * @param Request $request
     * @param Listing $listing
     * @return RedirectResponse
     */
    public function update(Request $request, Listing $listing): RedirectResponse
    {
        // Make sure logged in user is owner
        if ($listing->user_id != auth()->id()) {
            abort(403, 'Unauthorized Action');
        }

        $formFields = $request->validate([
            'title' => 'required',
            'company' => ['required'],
            'location' => 'required',
            'website' => 'required',
            'email' => ['required', 'email'],
            'tags' => 'required',
            'description' => 'required',
        ]);

        if ($request->hasFile('logo')) {
            $formFields['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $listing->update($formFields);


        return back()->with('message', 'Listing Updated Successfully!');
    }

    /**
     * Show Edit Form
     *
     * @param Request $request
     * @return \Illuminate\Foundation\Application|Redirector|RedirectResponse|Application
     */
    public function store(Request $request): \Illuminate\Foundation\Application|\Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse|Application
    {
        $formFields = $request->validate([
            'title' => 'required',
            'company' => ['required', Rule::unique('listings', 'company')],
            'location' => 'required',
            'website' => 'required',
            'email' => ['required', 'email'],
            'tags' => 'required',
            'description' => 'required',
        ]);

        if ($request->hasFile('logo')) {
            $formFields['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $formFields['user_id'] = auth()->id();

        Listing::create($formFields);


        return redirect('/')->with('message', 'Listing Created Successfully!');
    }

    /**
     * Show Create Form
     *
     * @return Application|Factory|View|\Illuminate\Foundation\Application
     */
    public function create(): \Illuminate\Foundation\Application|View|Factory|Application
    {
        return view('listings.create');
    }

    /**
     * Delete Listing
     *
     * @param Listing $listing
     * @return \Illuminate\Foundation\Application|Redirector|RedirectResponse|Application
     */
    public function destroy(Listing $listing): \Illuminate\Foundation\Application|Redirector|RedirectResponse|Application
    {
        // Make sure logged-in user is owner
        if ($listing->user_id != auth()->id()) {
            abort(403, 'Unauthorized Action');
        }
        $listing->delete();
        return redirect('/')->with('message', 'Listing deleted successfully');
    }

    public function manage()
    {
        return view(
            'listings.manage',
            ['listings' => auth()->user()->listings()->get()]);
    }
}

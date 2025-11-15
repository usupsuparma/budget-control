<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Login extends Component
{
    public $email, $password, $remember = false;

    public function login()
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required|min:5',
        ]);

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        session()->flash('error', 'Email atau password salah.');
    }

    /**
     * @return \Livewire\Features\SupportLayouts\LayoutView
     */
    public function render()
    {
        return view('livewire.auth.login')
            ->layout('layouts.auth');
    }
}

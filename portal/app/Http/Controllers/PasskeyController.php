<?php

namespace App\Http\Controllers;

use App\Models\Passkey;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Webauthn\AttestationStatement\AttestationObjectLoader;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialLoader;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;

class PasskeyController extends Controller
{
    private string $rpId = 'portal.traitor.dev';
    private string $rpName = 'Traitor.dev';

    private function loader(): PublicKeyCredentialLoader
    {
        $manager = AttestationStatementSupportManager::create();
        $manager->add(NoneAttestationStatementSupport::create());

        return PublicKeyCredentialLoader::create(AttestationObjectLoader::create($manager));
    }

    // -------------------------------------------------------------------------
    // Invite flow: validate reset token → log in → return creation options
    // -------------------------------------------------------------------------

    public function invitePasskeyOptions(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
        ]);

        $random = Str::random(32);

        $status = Password::reset(
            [...$request->only('email', 'token'), 'password' => $random, 'password_confirmation' => $random],
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])->setRememberToken(Str::random(60));
                $user->save();
                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PasswordReset) {
            return response()->json(['error' => __($status)], 422);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        Auth::login($user);
        $request->session()->regenerate();

        return $this->buildCreationOptions($user);
    }

    // -------------------------------------------------------------------------
    // Registration (authenticated)
    // -------------------------------------------------------------------------

    public function registerOptions(Request $request): \Illuminate\Http\JsonResponse
    {
        return $this->buildCreationOptions(Auth::user());
    }

    public function register(Request $request): \Illuminate\Http\JsonResponse
    {
        $options = PublicKeyCredentialCreationOptions::createFromString(
            session('passkey_register_options')
        );

        $credential = $this->loader()->load($request->getContent());

        if (!$credential->response instanceof AuthenticatorAttestationResponse) {
            abort(422, 'Invalid response type');
        }

        $factory = new CeremonyStepManagerFactory();
        $validator = AuthenticatorAttestationResponseValidator::create($factory->creationCeremony());

        $source = $validator->check(
            authenticatorAttestationResponse: $credential->response,
            publicKeyCredentialCreationOptions: $options,
            request: $this->rpId,
        );

        Passkey::create([
            'user_id'           => Auth::id(),
            'name'              => $request->input('name', 'Passkey'),
            'credential_id'     => bin2hex($source->publicKeyCredentialId),
            'credential_source' => json_encode($source),
        ]);

        session()->forget('passkey_register_options');

        return response()->json(['ok' => true]);
    }

    // -------------------------------------------------------------------------
    // Authentication (guest)
    // -------------------------------------------------------------------------

    public function authOptions(Request $request): \Illuminate\Http\JsonResponse
    {
        $options = PublicKeyCredentialRequestOptions::create(
            challenge: random_bytes(32),
            rpId: $this->rpId,
            userVerification: PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_REQUIRED,
        );

        session(['passkey_auth_options' => json_encode($options)]);

        return response()->json($options);
    }

    public function authenticate(Request $request): \Illuminate\Http\JsonResponse
    {
        $options = PublicKeyCredentialRequestOptions::createFromString(
            session('passkey_auth_options')
        );

        $credential = $this->loader()->load($request->getContent());

        if (!$credential->response instanceof AuthenticatorAssertionResponse) {
            abort(422, 'Invalid response type');
        }

        $passkey = Passkey::where('credential_id', bin2hex($credential->rawId))->firstOrFail();

        $factory = new CeremonyStepManagerFactory();
        $validator = AuthenticatorAssertionResponseValidator::create($factory->requestCeremony());

        $updatedSource = $validator->check(
            credentialId: $passkey->credentialSource(),
            authenticatorAssertionResponse: $credential->response,
            publicKeyCredentialRequestOptions: $options,
            request: $this->rpId,
            userHandle: null,
        );

        $passkey->update(['credential_source' => json_encode($updatedSource)]);

        Auth::login(User::findOrFail($passkey->user_id));
        $request->session()->regenerate();
        session()->forget('passkey_auth_options');

        return response()->json(['ok' => true]);
    }

    // -------------------------------------------------------------------------
    // Delete passkey (authenticated)
    // -------------------------------------------------------------------------

    public function destroy(Passkey $passkey): \Illuminate\Http\RedirectResponse
    {
        abort_if($passkey->user_id !== Auth::id(), 403);
        $passkey->delete();

        return back()->with('success', 'Passkey removed.');
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function buildCreationOptions(User $user): \Illuminate\Http\JsonResponse
    {
        $options = PublicKeyCredentialCreationOptions::create(
            rp: PublicKeyCredentialRpEntity::create($this->rpName, $this->rpId),
            user: PublicKeyCredentialUserEntity::create(
                name: $user->email,
                id: (string) $user->id,
                displayName: $user->name,
            ),
            challenge: random_bytes(32),
            authenticatorSelection: AuthenticatorSelectionCriteria::create(
                residentKey: AuthenticatorSelectionCriteria::RESIDENT_KEY_REQUIREMENT_REQUIRED,
                userVerification: AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_REQUIRED,
            ),
            attestation: PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE,
        );

        session(['passkey_register_options' => json_encode($options)]);

        return response()->json($options);
    }
}

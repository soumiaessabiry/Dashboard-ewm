@extends('layouts.user_type.auth')

@section('content')
    <div>

        @if (session('team_success'))
            <div id="teamSuccessMessage" class="alert alert-success">
                {{ session('team_success') }}
            </div>
        @endif

        @if ($errors->any())
            <div id="teamErrorMessage" class="alert alert-warning text-white fw-boldr">
                <ul>
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </ul>
            </div>
        @endif


        <div class="row">
            <div class="col-12">
                <div class="card mb-4 mx-4">
                    <div class="card-header pb-0">
                        <div class="d-flex flex-row justify-content-between">
                            <div>
                                <h5 class="mb-0 capitalize">Les équipes</h5>
                            </div>
                            <button style="background: #0a8897" class="btn capitalize btn-sm mb-0 border-none text-white"
                                type="button" data-bs-toggle="modal" data-bs-target="#addLigueModal">
                                + Ajouter une équipe
                            </button>
                        </div>
                    </div>

                    {{-- Modal Ajouter une équipe --}}
                    <div class="modal fade" id="addLigueModal" tabindex="-1" aria-labelledby="addLigueModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addLigueModalLabel">Ajouter une équipe</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form role="form text-left" method="POST" action="{{ route('teams.store') }}">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="teamName" class="form-label">Nom de l'équipe</label>
                                            <input type="text" class="form-control" id="teamName" name="team_name"
                                                required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="teamCode" class="form-label">Code équipe</label>
                                            <input type="number" class="form-control" id="teamCode" name="team_code"
                                                required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="leagueId" class="form-label">Ligue</label>
                                            <select name="league_id" id="leagueId" class="form-control" required>
                                                @foreach ($leagues as $league)
                                                    <option value="{{ $league->id }}">{{ $league->league_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Créer l'équipe</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Fin modal ajout équipe --}}
                    <div class="card-body px-0 pb-2 pt-3">
                        <div class="table-responsive p-0">
                            <table class="table mt-3 responsive" id="teamsTable" >
                                <thead>
                                    <tr>
                                        <th>
                                            ID</th>
                                        <th>
                                            Nom d'équipe</th>
                                        <th class="text-center">
                                            Nombre de joueurs</th>
                                        <th class="text-center">
                                            Code équipe</th>
                                        <th class="text-center">
                                            Nom de ligue</th>
                                        <th class="text-center">
                                            Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($teams as $team)
                                        <tr>
                                            <td class="text-center py-1 " style="color: #000000">{{ $team->id }}</td>
                                            <td class="text-center">{{ $team->team_name }}</td>
                                            <td class="text-center py-1">
                                                <button style="color: #000000" class="btn btn-link fs-6 px-3 fw-normal "
                                                    type="button" data-bs-toggle="modal"
                                                    data-bs-target="#viewMembersModal{{ $team->id }}">
                                                    {{ $team->number_of_players }}
                                                </button>
                                                <button class="btn btn-link fs-6  text-primary px-0" type="button"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#viewMembersModal{{ $team->id }}">
                                                    <i class="fa fa-eye"></i>
                                                </button>
                                            </td>
                                            <td class="text-center">{{ $team->team_code }}</td>
                                            <td class="text-center">{{ $team->league->league_name }}</td>
                                            <td class="text-start py-1 ">
                                                <div class="flex-row gap-2 d-flex justify-content-center  ">
                                                    <button style="background: #0a8897"
                                                        class="btn capitalize  px-1  mb-0 border-none text-white"
                                                        type="button" data-bs-toggle="modal"
                                                        data-bs-target="#addMemberModal{{ $team->id }}">
                                                        + Ajouter un membre
                                                    </button>
                                                    <div
                                                        class="flex-row d-flex gap-1 align-items-center justify-content-center ">

                                                        <button class="btn px-1 btn-link mb-0 text-warning" type="button"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editTeamModal{{ $team->id }}">
                                                            <i class="fa fa-pencil-square-o fs-5"></i>
                                                        </button>
                                                        <button class="btn px-1 py-0 mb-0 btn-link text-danger"
                                                            type="button" data-bs-toggle="modal"
                                                            data-bs-target="#deleteTeamModal{{ $team->id }}">
                                                            <i class="fa fa-trash-o fs-5"></i>
                                                        </button>



                                                    </div>
                                                </div>


                                            </td>
                                        </tr>

                                        {{-- Modal Voir les membres --}}
                                        <div class="modal fade" id="viewMembersModal{{ $team->id }}" tabindex="-1"
                                            aria-labelledby="viewMembersModalLabel{{ $team->id }}"
                                            aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"
                                                            id="viewMembersModalLabel{{ $team->id }}">Les membres
                                                            de :
                                                            <span
                                                                class="fw-bolder capitalize">{{ $team->team_name }}</span>
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="list-group">
                                                            @foreach ($team->users as $user)
                                                                <div
                                                                    class="list-group-item d-flex justify-content-between align-items-center">
                                                                    {{ $user->username }}
                                                                    <form
                                                                        action="{{ route('teams.destroyMember', ['team' => $team->id, 'user' => $user->id]) }}"
                                                                        method="POST">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit"
                                                                            class="btn btn-danger btn-sm mb-0">Supprimer</button>
                                                                    </form>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- Fin modal voir les membres --}}

                                        {{-- Modal Ajouter un membre --}}
                                        <div class="modal fade" id="addMemberModal{{ $team->id }}" tabindex="-1"
                                            aria-labelledby="addMemberModalLabel{{ $team->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"
                                                            id="addMemberModalLabel{{ $team->id }}">Ajouter un
                                                            membre
                                                            à {{ $team->team_name }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form role="form text-left" method="POST"
                                                            action="{{ route('teams.storeMember', $team->id) }}">
                                                            @csrf
                                                            <div class="mb-3">
                                                                <label for="username{{ $team->id }}"
                                                                    class="form-label">Nom
                                                                    d'utilisateur</label>
                                                                <input type="text" class="form-control"
                                                                    id="username{{ $team->id }}" name="username"
                                                                    required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="email{{ $team->id }}"
                                                                    class="form-label">Email</label>
                                                                <input type="email" class="form-control"
                                                                    id="email{{ $team->id }}" name="email"
                                                                    required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="password{{ $team->id }}"
                                                                    class="form-label">Mot de
                                                                    passe</label>
                                                                <input type="password" class="form-control"
                                                                    id="password{{ $team->id }}" name="password"
                                                                    required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="password_confirmation{{ $team->id }}"
                                                                    class="form-label">Confirmer le mot de passe</label>
                                                                <input type="password" class="form-control"
                                                                    id="password_confirmation{{ $team->id }}"
                                                                    name="password_confirmation" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="country{{ $team->id }}"
                                                                    class="form-label">Pays</label>
                                                                <input type="text" class="form-control"
                                                                    id="country{{ $team->id }}" name="country"
                                                                    required>
                                                            </div>
                                                            <button type="submit" class="btn btn-primary">Ajouter le
                                                                membre</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- Fin modal ajout membre --}}
                                        <!-- Modal Modifier équipe -->
                                        <div class="modal fade" id="editTeamModal{{ $team->id }}" tabindex="-1"
                                            aria-labelledby="editTeamModalLabel{{ $team->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"
                                                            id="editTeamModalLabel{{ $team->id }}">Modifier
                                                            {{ $team->team_name }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form role="form text-left" method="POST"
                                                            action="{{ route('teams.update', $team->id) }}">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="mb-3">
                                                                <label for="editTeamName{{ $team->id }}"
                                                                    class="form-label">Nom de
                                                                    l'équipe</label>
                                                                <input type="text" class="form-control"
                                                                    id="editTeamName{{ $team->id }}" name="team_name"
                                                                    value="{{ $team->team_name }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="editTeamCode{{ $team->id }}"
                                                                    class="form-label">Code
                                                                    équipe</label>
                                                                <input type="text" class="form-control"
                                                                    id="editTeamCode{{ $team->id }}" name="team_code"
                                                                    value="{{ $team->team_code }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="editLeagueId{{ $team->id }}"
                                                                    class="form-label">Ligue</label>
                                                                <select name="league_id"
                                                                    id="editLeagueId{{ $team->id }}"
                                                                    class="form-control" required>
                                                                    @foreach ($leagues as $league)
                                                                        <option value="{{ $league->id }}"
                                                                            {{ $team->league_id == $league->id ? 'selected' : '' }}>
                                                                            {{ $league->league_name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <button type="submit" class="btn btn-primary">Enregistrer les
                                                                modifications</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Fin modal Modifier équipe -->
                                        <!-- Modal Supprimer équipe -->
                                        <div class="modal fade" id="deleteTeamModal{{ $team->id }}" tabindex="-1"
                                            aria-labelledby="deleteTeamModalLabel{{ $team->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"
                                                            id="deleteTeamModalLabel{{ $team->id }}">Confirmer la
                                                            suppression</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Êtes-vous sûr de vouloir supprimer l'équipe
                                                            "{{ $team->team_name }}" ? Cette action est irréversible.</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Annuler</button>
                                                        <form action="{{ route('teams.destroy', $team->id) }}"
                                                            method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="btn btn-danger">Supprimer</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Fin modal Supprimer équipe -->
                                    @endforeach
                                </tbody>
                            </table>
                            {{-- <div class="d-flex justify-content-center">
                                {{ $teams->links('components.pagination.custom') }}
                            </div> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')

<script>
       $(document).ready(function() {
            $('#teamsTable').DataTable({
                "oLanguage": {
                    "sLengthMenu": "_MENU_", // Keep _MENU_ for length menu
                    "sSearch": "Rechercher",
                    "zeroRecords": "Aucun résultat retourné"

                },
                "info": false,
                "paging": true,


                "pagingType": "numbers",
                "lengthMenu": [5, 7, 10, 50, 75], // Personalisation du nombre de lignes par page
                "pageLength": 5, // Nombre de lignes affichées par défaut
                "searching": true, // Activer la recherche
                "ordering": true, // Activer le tri
                "order": [
                    [0, 'desc']
                ], // Trier par défaut par la première colonne

                "buttons": ['copy', 'csv', 'excel', 'pdf'] // Boutons d'exportation

            });
        });
    // Masquer le message de succès après 5 secondes
    setTimeout(function() {
        document.getElementById('teamSuccessMessage').style.display = 'none';
    }, 5000);

    // Masquer le message d'erreur après 5 secondes
    setTimeout(function() {
        document.getElementById('teamErrorMessage').style.display = 'none';
    }, 5000);
</script>
@endpush


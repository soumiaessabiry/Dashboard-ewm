@extends('layouts.user_type.auth')
@section('content')

    @if (session('message_success'))
        <div id="userSuccessMessage" class="alert alert-success">
            {{ session('message_success') }}
        </div>
    @endif
    @if ($errors->any())
        <div id="userErrorMessage" class="alert alert-warning text-white fw-bold ">
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
                            <h5 class="mb-0 capitalize">Les utilisateurs</h5>
                        </div>
                        <button style="background: #0a8897" class="btn capitalize btn-sm mb-0 border-none text-white"
                            type="button" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            Ajouter un utilisateur
                        </button>
                    </div>
                </div>

                <table class="table mt-3" id="usersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th class="px-2">Nom d'utilisateur</th>
                            <th class="px-2">Email</th>
                            <th class="px-2">Équipe</th>
                            <th class="px-2">Rôle</th>
                            <th class="px-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td class="text-center py-1">{{ $user->id }}</td>
                                <td class="text-center py-1">{{ $user->username }}</td>
                                <td class="text-center py-1">{{ $user->email }}</td>
                                <td class="text-center py-1">{{ $user->team ? $user->team->team_name : 'Aucune' }}</td>
                                <td>{{ $user->role }}</td>
                                <td class="py-1">
                                    <button type="button" class="btn px-1 btn-link mb-0 text-warning"
                                        data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}">
                                        <i class="fa fa-pencil-square-o fs-5"></i>
                                    </button>
                                    <!-- Trigger Modal for Delete Confirmation -->
                                    <button type="button" class="btn px-1 py-0 mb-0 btn-link text-danger"
                                        data-bs-toggle="modal" data-bs-target="#confirmDeleteModal{{ $user->id }}">
                                        <i class="fa fa-trash-o fs-5"></i>
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal de mise à jour de l'utilisateur -->
                            <div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1"
                                aria-labelledby="editUserModalLabel{{ $user->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editUserModalLabel{{ $user->id }}">Modifier
                                                l'utilisateur</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form method="POST" action="{{ route('users.update', $user->id) }}">
                                                @csrf
                                                @method('PUT')
                                                <div class="mb-3">
                                                    <label for="username{{ $user->id }}" class="form-label">Nom
                                                        d'utilisateur</label>
                                                    <input type="text" class="form-control"
                                                        id="username{{ $user->id }}" name="username"
                                                        value="{{ $user->username }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="email{{ $user->id }}" class="form-label">Email</label>
                                                    <input type="email" class="form-control"
                                                        id="email{{ $user->id }}" name="email"
                                                        value="{{ $user->email }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="team_id{{ $user->id }}"
                                                        class="form-label">Équipe</label>
                                                    <select class="form-control" id="team_id{{ $user->id }}"
                                                        name="team_id">
                                                        <option value="">Aucune</option>
                                                        @foreach ($teams as $team)
                                                            <option value="{{ $team->id }}"
                                                                {{ $user->team_id == $team->id ? 'selected' : '' }}>
                                                                {{ $team->team_name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="password{{ $user->id }}" class="form-label">Mot de
                                                        passe</label>
                                                    <input type="password" class="form-control"
                                                        id="password{{ $user->id }}" name="password">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="password_confirmation{{ $user->id }}"
                                                        class="form-label">Confirmer le mot de passe</label>
                                                    <input type="password" class="form-control"
                                                        id="password_confirmation{{ $user->id }}"
                                                        name="password_confirmation">
                                                </div>
                                                <button type="submit" class="btn btn-primary">Mettre à jour</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal de confirmation de suppression -->
                            <div class="modal fade" id="confirmDeleteModal{{ $user->id }}" tabindex="-1"
                                aria-labelledby="confirmDeleteModalLabel{{ $user->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="confirmDeleteModalLabel{{ $user->id }}">
                                                Confirmer la suppression</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Êtes-vous sûr de vouloir supprimer l'utilisateur "{{ $user->username }}" ?
                                            </p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Annuler</button>
                                            <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                                                style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">Supprimer</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>

                <!-- Liens de pagination -->
                <div class="d-flex justify-content-center">
                    {{ $users->links('components.pagination.custom') }}
                </div>
            </div>

            <!-- Modal d'ajout d'utilisateur -->
            <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addUserModalLabel">Ajouter un utilisateur</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form method="POST" action="{{ route('users.store') }}">
                                @csrf
                                <div class="mb-3">
                                    <label for="username" class="form-label">Nom d'utilisateur</label>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="team_id" class="form-label">Équipe</label>
                                    <select class="form-control" id="team_id" name="team_id">
                                        <option value="">Aucune</option>
                                        @foreach ($teams as $team)
                                            <option value="{{ $team->id }}">{{ $team->team_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Mot de passe</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirmer le mot de
                                        passe</label>
                                    <input type="password" class="form-control" id="password_confirmation"
                                        name="password_confirmation" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Ajouter</button>
                            </form>
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
            $('#usersTable').DataTable({
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
            document.getElementById('userSuccessMessage').style.display = 'none';
        }, 5000);

        // Masquer le message d'erreur après 5 secondes
        setTimeout(function() {
            document.getElementById('userErrorMessage').style.display = 'none';
        }, 5000);
    </script>
@endpush
{{-- <script>
    $(document).ready(function() {
        // Initialiser DataTable
        var table = $('#usersTable').DataTable({
            ajax: "{{ route('users.index') }}",

            processing: true,
    serverSide: true,

            "oLanguage": {
                 "sLengthMenu": "_MENU_ ", // **dont remove _MENU_ keyword**
                 "sSearch": "Rechercher ",

    zeroRecords: "Aucun résultat retourné",
            },
            "info" : false,


            ajax: "{{ route('users.index') }}",
            columns: [
                { data: 'id', name: 'id', orderable: true },
                { data: 'username', name: 'username', orderable: true },
                { data: 'email', name: 'email', orderable: true },
                { data: 'team', name: 'team.team_name', orderable: true },
                { data: 'role', name: 'role', orderable: true },
                { data: 'actions', name: 'actions', orderable: false, searchable: false },
            ],
            // Configuration de la langue (optionnel)
            language: {
                "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/French.json"


            },


            // Personnalisation du nombre de lignes par page
            lengthMenu: [5, 7, 10, 50, 75, ],
            pageLength: 5, // Nombre de lignes affichées par défaut
            // Activer la recherche et le tri sur toutes les colonnes
            searching: true,
            ordering: true, // Activer le tri
            // Ajouter les icônes pour le tri
            order: [[ 0, 'dec' ]], // Trier par défaut par la première colonne,
            buttons: [
                'copy', 'csv', 'excel', 'pdf' // Boutons d'exportation
            ],

            // Personnalisation de la pagination
            pagingType: "numbers"
        });
        $('#usersTable').on('click', '.editUserButton', function() {
            var userId = $(this).data('id');
            var username = $(this).data('username');
            var email = $(this).data('email');
            var teamId = $(this).data('team-id');

            // Pré-remplir le formulaire de modification
            $('#editUserId').val(userId);
            $('#editUsername').val(username);
            $('#editEmail').val(email);
            $('#editTeamId').val(teamId);
            $('#editUserForm').attr('action', '/users/' + userId); // Correction ici

            // Afficher le modal de modification
            $('#editUserModal').modal('show');
        });

        // Gestionnaire d'événement pour le bouton Supprimer
        $('#usersTable').on('click', '.deleteUserButton', function() {
            var userId = $(this).data('id');
            var username = $(this).data('username');

            // Afficher le nom de l'utilisateur à supprimer dans le modal de confirmation
            $('#deleteUserName').text(username);

            // Mettre à jour l'action du formulaire de suppression
            $('#deleteUserForm').attr('action', '/users/' + userId);

            // Afficher le modal de confirmation de suppression
            $('#confirmDeleteModal').modal('show');
        });
        // Masquer les messages après quelques secondes
        setTimeout(function () {
            $('#userSuccessMessage, #userErrorMessage').fadeOut('slow');
        }, 3000); // 3000 millisecondes = 3 secondes
    });
</script> --}}

{{-- </script>
@endpush --}}

@extends('layouts.user_type.auth')

@section('content')
    <div>


        @if (Session::has('ligue_success'))
            <div id="liguSuccessMessage" class="alert alert-success text-green  fw-bold">
                {{ Session::get('ligue_success') }}
            </div>
        @endif
        @if (Session::has('ligue_error'))
            <div id="liguErrorMessage"  class="alert alert-warning text-white fw-bold "role="alert">
                {{ Session::get('ligue_error') }}
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card mb-4 mx-4">
                    <div class="card-header pb-0">
                        <div class="d-flex flex-row justify-content-between">
                            <div>
                                <h5 class="mb-0 capitalize">Les Ligues</h5>
                            </div>

                            <button style="background: #0a8897" class="btn capitalize btn-sm mb-0 border-none text-white"
                                type="button" data-bs-toggle="modal" data-bs-target="#addLigueModal">
                                + Ajouter une ligue
                            </button>
                        </div>
                    </div>
                    <div class="modal fade" id="addLigueModal" tabindex="-1" aria-labelledby="addLigueModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addLigueModalLabel">Ajouter une Ligue</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">

                                    <form role="form text-left" method="POST" action="{{ route('leagues.store') }}">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="ligueName" class="form-label">Nom de la ligue</label>
                                            <input type="text" class="form-control" id="ligueName" name="league_name"
                                                required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="teamCount" class="form-label">Nombre d'équipes</label>
                                            <input type="number" class="form-control" id="teamCount"
                                                name="number_of_teams">
                                        </div>
                                        <button type="submit" class="btn btn-primary">Ajouter</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-0  pb-2 pt-3">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0" id="leagueTable">
                                <thead>
                                    <tr>
                                        <th class="text-center px-2">
                                            ID
                                        </th>
                                        <th class="text-center px-2">
                                            Nom de ligue
                                        </th>
                                        <th class="text-center px-2">
                                            Nombre total d'équipes</th>
                                        <th class="text-center px-2">
                                            Équipes ajoutées</th>

                                        <th class="text-center px-2">
                                            Action
                                        </th>


                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($leagues as $league)
                                        <tr>
                                            <td class=" text-center py-1">
                                                {{ $league->id }}
                                            </td>
                                            <td class="text-center py-1">
                                                {{ $league->league_name }}
                                            </td>
                                            <td class="text-center py-1">
                                                {{ $league->number_of_teams }}
                                            </td>
                                            <td class="text-center py-1">
                                                {{ $league->teams->count() }}
                                            </td>
                                            <td class="text-center py-1">
                                                <button type="button" class="btn px-1 btn-link mb-0 text-warning"
                                                    href={{ $league->id }} class="mx-3" data-bs-toggle="modal"
                                                    data-bs-target="#editLigueModal{{ $league->id }}">
                                                    <i class="fa fa-pencil-square-o fs-5"></i>
                                                </button>

                                                <button type="button" class="btn px-1 py-0 mb-0 btn-link text-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#confirmDeleteModal{{ $league->id }}">
                                                    <i class="fa fa-trash-o fs-5"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <!-- Modal de mise à jour de la Ligue -->
                                        <div class="modal fade" id="editLigueModal{{ $league->id }}" tabindex="-1"
                                            aria-labelledby="editLigueModalLabel{{ $league->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"
                                                            id="editLigueModalLabel{{ $league->id }}">Modifier la Ligue
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form role="form text-left" method="POST"
                                                            action="{{ route('leagues.update', $league->id) }}">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="mb-3">
                                                                <label for="editLigueName{{ $league->id }}"
                                                                    class="form-label">Nom de la ligue</label>
                                                                <input type="text" class="form-control"
                                                                    id="editLigueName{{ $league->id }}"
                                                                    name="league_name" value="{{ $league->league_name }}"
                                                                    required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="editTeamCount{{ $league->id }}"
                                                                    class="form-label">Nombre d'équipes</label>
                                                                <input type="number" class="form-control"
                                                                    id="editTeamCount{{ $league->id }}"
                                                                    name="number_of_teams"
                                                                    value="{{ $league->number_of_teams }}">
                                                            </div>
                                                            <button type="submit" class="btn btn-primary">Mettre à
                                                                jour</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal fade" id="confirmDeleteModal{{ $league->id }}"
                                            tabindex="-1" aria-labelledby="confirmDeleteModalLabel{{ $league->id }}"
                                            aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"
                                                            id="confirmDeleteModalLabel{{ $league->id }}">Confirmer la
                                                            Suppression</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Êtes-vous sûr de vouloir supprimer la ligue
                                                            "{{ $league->league_name }}" ?</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Annuler</button>
                                                        <form action="{{ route('leagues.destroy', $league->id) }}"
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
                                    @endforeach

                                </tbody>

                            </table>
                            <div class="d-flex justify-content-center">
                                {{ $leagues->links('components.pagination.custom') }}
                            </div>
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
            $('#leagueTable').DataTable({
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
        document.getElementById('liguSuccessMessage').style.display = 'none';
    }, 5000);

    // Masquer le message d'erreur après 5 secondes
    setTimeout(function() {
        document.getElementById('liguErrorMessage').style.display = 'none';
    }, 5000);
</script>
@endpush


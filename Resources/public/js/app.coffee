# Démarrage de l'application
$ ->
    new Application()
    console.log "Starting applicaiton"

# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

class Application extends Backbone.View
    el: $("#container")

    initialize: ->
        this.jobsView = new JobsView()

# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

# Représente un job
class Job extends Backbone.Model
    defaults:
        id: null
        name: null
        dateCreation: null
        dateUpdate: null

# Représente une tache
class Task extends Backbone.Model
    defaults:
        id: null
        name: null
        dateCreation: null
        dateUpdate: null

# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

# La collection des jobs.
class JobsCollection extends Backbone.Collection
    model: Job
    url: "http://ping.me/app_dev.php/api/v1/jobs.json"

# La collection de taches d'un job.
class TaskCollection extends Backbone.Collection
    model: Task
    id: null

    initialize: (id) ->
        this.id = id

    url: ->
        if @id
            "http://ping.me/app_dev.php/api/v1/tasks/" + @id + ".json"
        else
            console.log "There is no current id assigned"
            ""

# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

# La vue d'un seul job
class JobView extends Backbone.View
    tagName: "li"

    tasks: null

    events:
        'click *' : 'afficherJob'

    initialize: ->
        this.model.bind "change", this.render

    render: ->
        tmpl = _.template($("#jobTemplate").html())
        this.$el.html(tmpl(this.model.toJSON()))
        this

    afficherJob: (event) ->
        id = $(event.currentTarget).closest("div.infos").data("id")
        this.tasks = new TaskCollection(id)
        this.tasks.fetch()

# La vue de tout les jobs
class JobsView extends Backbone.View
    $el: $("#jobs")

    initialize: ->
        this.collection = new JobsCollection()
        this.collection.bind "add", this.renderJob, this
        this.collection.fetch()

    renderJob: (job) ->
        this.collection.add job
        jobView = new JobView
            model: job
        $("ul.jobs").append(jobView.render().el)




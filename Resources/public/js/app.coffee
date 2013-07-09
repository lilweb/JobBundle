# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

baseUrl = "http://ping.me/app_dev.php"

# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$ ->
    console.log "Starting application"
    application = new Application()

    # L'event ne semble pas être catché dans l'application, on le sort donc.
    $(".loadNextDay").click (event) ->
        event.preventDefault()
        event.stopPropagation()
        application.loadNextDay()

# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

class Application extends Backbone.View
    el: $("#application")
    lastId: null
    lastDate: null

    # Chargement de la premiere journée ie. ajd
    initialize: ->
        this.lastDate = new Date()
        this.lastId = 0
        this.loadDay(this.lastDate)

    loadDay: (date) ->
        dayView = new DayView(date, this.lastId + 1)
        $("#application .loadNextDay").before(dayView.render())

    loadNextDay: ->
        this.lastId = this.lastId + 1
        this.lastDate = new Date(this.lastDate.setDate(this.lastDate.getDate() - 1))
        this.loadDay(this.lastDate)

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
        status: null

# Représente une tache
class Task extends Backbone.Model
    defaults:
        id: null
        name: null
        dateCreation: null
        dateUpdate: null
        status: null

# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

# La collection des jobs.
class JobsCollection extends Backbone.Collection
    model: Job

    initialize: (date) ->
        this.date = date

    url: ->
        baseUrl + "/api/v1/jobs/" + this.date.getFullYear() + "/" + (this.date.getMonth() + 1)+ "/" + this.date.getDate() + "/list.json"

# La collection de taches d'un job.
class TaskCollection extends Backbone.Collection
    model: Task
    id: null

    initialize: (id) ->
        this.id = id

    url: ->
        if @id
            baseUrl + "/api/v1/tasks/" + @id + ".json"

# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

# La vue d'une seule tache
class TaskView extends Backbone.View
    tagName: "li"

    events:
        "click .showMessage" : "showMessage"
        "click .restartTask" : "restart"

    initialize: ->
        this.model.bind "change", this.render

    render: ->
        tmpl = _.template($("#taskTemplate").html())
        this.$el.html(tmpl(this.model.toJSON()))
        this

    showMessage: (event) ->
        event.preventDefault()
        event.stopPropagation()
        alert this.model.toJSON().message

    restart: (event) ->
        event.preventDefault()
        event.stopPropagation()

        if window.confirm "Vous êtes sur de vouloir relancer cette tache ainsi que les suivantes?"
            console.log "Restart", this.model.toJSON().id

# La vue des taches d'un job
class TasksView extends Backbone.View
    id: null

    setId: (id) ->
        this.id = id
        this.collection = new TaskCollection(id)
        this.collection.bind "add", this.renderTask, this
        this.collection.fetch().render

    render: ->
        tmpl = _.template($("#tasksTemplate").html())
        tmpl
            "id": @id

    renderTask: (task) ->
        this.collection.add task
        taskView = new TaskView
            model: task
        $("#tasks-" + @id).append(taskView.render().el)

# La vue d'un seul job
class JobView extends Backbone.View
    tagName: "li"
    tasksView: null

    events:
        'click .infos' : 'afficherTasks'

    initialize: ->
        this.model.bind "change", this.render

    render: ->
        tmpl = _.template($("#jobTemplate").html())
        this.$el.html(tmpl(this.model.toJSON()))
        this

    afficherTasks: (event) ->
        if not this.tasksView?
            id = $(event.currentTarget).closest("div.infos").data("id")
            this.tasksView = new TasksView
            this.tasksView.setId id
            this.$el.after(this.tasksView.render())
            this.$el.toggleClass "open"
        else
            this.$el.next("ul.tasks").remove()
            this.tasksView = null
            this.$el.toggleClass "open"

# La vue d'une journee
class DayView extends Backbone.View
    id: null
    date: null

    initialize: (date, id) ->
        this.id = id
        this.date = date
        this.render

        this.collection = new JobsCollection(date)
        this.collection.bind "add", this.renderJob, this
        this.collection.fetch
            success: =>
                if this.collection.length == 0
                    this.renderEmpty()

    render: ->
        tmpl = _.template($("#dayTemplate").html())
        tmpl
            "id": this.id
            "date" : this.date

    renderEmpty: ->
        $("#day-" + this.id + " ul.jobs").append(_.template($("#jobTemplateEmpty").html()))

    renderJob: (job) ->
        this.collection.add job
        jobView = new JobView
            model: job
        $("#day-" + this.id + " ul.jobs").append(jobView.render().el)

